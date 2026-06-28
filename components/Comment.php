<?php

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$baseUrlJs = json_encode($baseUrl);
$currentUser = $_SESSION['user'] ?? null;
if ($currentUser && empty($currentUser['avatar'])) {
    require_once __DIR__ . '/../api/Helpers/DatabaseHelper.php';
    $dbUser = fetchOne("SELECT avatar FROM users WHERE id = ? LIMIT 1", [$currentUser['id']]);
    if ($dbUser && !empty($dbUser['avatar'])) {
        $currentUser['avatar'] = $dbUser['avatar'];
        $_SESSION['user']['avatar'] = $dbUser['avatar'];
    }
}
$currentUserJs = json_encode($currentUser);

$pageScripts = ($pageScripts ?? '') . '
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    const baseUrl = ' . $baseUrlJs . ';
    const currentUser = ' . $currentUserJs . ';

    function renderCommentAvatar(user, size) {
        size = size || 42;
        var avatar = user && user.avatar ? user.avatar : "";
        var name = user && user.name ? user.name : "User";
        var initial = name.charAt(0).toUpperCase();

        if (avatar) {
            return $("<img>")
                .attr("src", avatar)
                .attr("alt", name)
                .addClass("rounded-circle flex-shrink-0")
                .css({ width: size + "px", height: size + "px", objectFit: "cover" });
        }

        var colorPalettes = [
            { bg: "#f1f3f5", text: "#495057" }, // Slate
            { bg: "#edf2ff", text: "#3b5bdb" }, // Muted Blue
            { bg: "#f3f0ff", text: "#845ef7" }, // Muted Purple
            { bg: "#e6fcf5", text: "#0ca678" }, // Muted Teal
            { bg: "#fff0f6", text: "#d6336c" }  // Muted Pink
        ];
        
        var charSum = 0;
        for (var i = 0; i < name.length; i++) {
            charSum += name.charCodeAt(i);
        }
        var palette = colorPalettes[charSum % colorPalettes.length];

        return $("<div>")
            .addClass("rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold")
            .css({ 
                width: size + "px", 
                height: size + "px", 
                backgroundColor: palette.bg,
                color: palette.text,
                fontSize: Math.floor(size * 0.44) + "px",
                border: "1px solid rgba(0,0,0,0.06)"
            })
            .text(initial);
    }

    function canDeleteComment(comment) {
        return !!(comment && (comment.isOwner || comment.canDelete));
    }

    function renderCommentDeleteButton(comment, blogId) {
        if (!canDeleteComment(comment)) return null;

        return $("<button>")
            .attr("type", "button")
            .addClass("btn btn-sm btn-link text-muted p-0 text-decoration-none comment-delete-button")
            .css({ fontSize: "0.85rem" })
            .attr("data-comment-id", comment.id)
            .attr("data-blog-id", blogId)
            .text("Delete");
    }

    function renderCommentReplyButton(comment) {
        return $("<button>")
            .attr("type", "button")
            .addClass("btn btn-sm btn-link text-muted p-0 text-decoration-none comment-reply-button")
            .css({ fontSize: "0.85rem" })
            .attr("data-comment-id", comment.id)
            .attr("data-username", comment.user && comment.user.username ? comment.user.username : "")
            .text("Reply");
    }

    function renderCommentsEmptyState(message) {
        return $("<div>")
            .addClass("text-center text-muted py-5 px-3 d-flex flex-column align-items-center justify-content-center gap-2 h-100")
            .append(
                $("<i>").addClass("bi bi-chat-left-dots fs-1 text-secondary"),
                $("<div>").addClass("fw-semibold text-body").text("No comments yet"),
                $("<div>").addClass("small").text(message || "Be the first to start the conversation.")
            );
    }

    function getCommentProfileUrl(user) {
        var userId = user && user.id ? parseInt(user.id, 10) : 0;
        return userId ? baseUrl + "/user/profile?id=" + userId : "#";
    }

    function createProfileLink(user, className, content) {
        var $link = $("<a>")
            .attr("href", getCommentProfileUrl(user))
            .addClass(className || "")
            .attr("title", user && user.name ? user.name : "View profile");

        if (typeof content === "function") {
            $link.append(content());
            return $link;
        }

        if (content != null) {
            $link.text(content);
        }

        return $link;
    }

    function parseCommentTimestamp(timestamp) {
        if (!timestamp) return null;

        var normalized = String(timestamp).trim().replace(" ", "T");
        if (!normalized.endsWith("Z") && normalized.indexOf("+") === -1 && (normalized.length < 19 || normalized.indexOf("-", 11) === -1)) {
            normalized += "Z";
        }
        var date = new Date(normalized);
        return isNaN(date.getTime()) ? null : date;
    }

    function formatCommentTimestamp(timestamp) {
        var date = parseCommentTimestamp(timestamp);
        if (!date) return "";

        var diffMs = Date.now() - date.getTime();
        if (diffMs < 0) diffMs = 0;

        var diffSeconds = Math.floor(diffMs / 1000);
        if (diffSeconds < 60) return "Just now";

        var diffMinutes = Math.floor(diffSeconds / 60);
        if (diffMinutes < 60) return diffMinutes + " min ago";

        var diffHours = Math.floor(diffMinutes / 60);
        if (diffHours < 24) return diffHours + " hr ago";

        var diffDays = Math.floor(diffHours / 24);
        if (diffDays === 1) return "Yesterday";

        return diffDays + " days ago";
    }

    function renderCommentNode(comment, blogId, level) {
        level = level || 0;
        var user = comment.user || {};
        var isReply = level > 0;
        var avatarSize = isReply ? 34 : 42;

        var $item = $("<div>")
            .addClass("comment-node d-flex flex-column mb-3")
            .attr("data-id", comment.id);

        var $mainRow = $("<div>").addClass("d-flex gap-3 align-items-start");

        $mainRow.append(
            createProfileLink(user, "flex-shrink-0 text-decoration-none", function () {
                var $avatar = renderCommentAvatar(user, avatarSize);
                if ($avatar.is("img")) {
                    $avatar.attr("loading", "lazy");
                }
                return $avatar;
            })
        );

        var $content = $("<div>").addClass("flex-grow-1 min-w-0");
        var $title = $("<div>").addClass("d-flex align-items-baseline gap-2 mb-1");
        
        var $name = createProfileLink(user, "fw-bold text-dark text-decoration-none")
            .css({ fontSize: isReply ? "0.9rem" : "0.95rem" })
            .text(user.name || "User");
            
        var $metaLine = $("<span>").addClass("text-muted small");
        $metaLine.text("@" + (user.username || "unknown") + " • " + formatCommentTimestamp(comment.created_at));

        $title.append($name, $metaLine);
        $content.append($title);
        
        $content.append(
            $("<div>")
                .addClass("text-dark mb-2")
                .css({ 
                    fontSize: isReply ? "0.88rem" : "0.92rem", 
                    lineHeight: "1.5",
                    wordBreak: "break-word"
                })
                .text(comment.comment || "")
        );

        var $actions = $("<div>").addClass("d-flex align-items-center gap-3");
        
        var $replyBtn = renderCommentReplyButton(comment)
            .removeClass("btn btn-sm btn-link text-muted p-0 text-decoration-none")
            .addClass("comment-action-link");
        $actions.append($replyBtn);

        var $deleteButton = renderCommentDeleteButton(comment, blogId);
        if ($deleteButton) {
            $deleteButton
                .removeClass("btn btn-sm btn-link text-muted p-0 text-decoration-none")
                .addClass("comment-action-link delete-link");
            $actions.append($deleteButton);
        }

        $content.append($actions);
        $mainRow.append($content);
        $item.append($mainRow);

        if (comment.replies && comment.replies.length) {
            var $repliesContainer = $("<div>")
                .addClass("replies-container")
                .css({
                    borderLeft: "2px solid #eaecf0",
                    marginLeft: Math.floor(avatarSize / 2) + "px",
                    paddingLeft: "20px",
                    marginTop: "8px"
                });

            comment.replies.forEach(function (reply) {
                var $replyNode = renderCommentNode(reply, blogId, level + 1);
                $repliesContainer.append($replyNode);
            });

            $item.append($repliesContainer);
        }

        return $item;
    }

    function renderComments(comments) {
        var $commentsList = $("#commentsList");
        $commentsList.empty();

        if (!comments || !comments.length) {
            $commentsList.append(renderCommentsEmptyState("Be the first to start the conversation."));
            return;
        }

        var blogId = $("#commentsModal").attr("data-blog-id") || $("#commentsModal").data("blog-id");
        comments.forEach(function (comment) {
            var $node = renderCommentNode(comment, blogId, 0);
            $commentsList.append($node);
        });
    }

    function showCommentsEmptyState(message) {
        $("#commentsList")
            .empty()
            .append(renderCommentsEmptyState(message || "Be the first to start the conversation."));
        $("#commentsCount").text("0 comments");
    }

    function updateCommentsCount(count) {
        var total = parseInt(count, 10) || 0;
        $("#commentsCount").text(total === 1 ? "1 comment" : total + " comments");
    }

    function showCommentsModalAlert(message, type) {
        var alertType = type || "danger";
        $("#commentsModalAlert")
            .removeClass("d-none alert-danger alert-success alert-warning alert-info")
            .addClass("alert-" + alertType)
            .text(message || "");
    }

    function hideCommentsModalAlert() {
        $("#commentsModalAlert")
            .addClass("d-none")
            .removeClass("alert-danger alert-success alert-warning alert-info")
            .text("");
    }

    function clearReplyState() {
        replyingToCommentId = null;
        replyingToUsername = null;
        $("#replyingToUsername").text("");
        $("#replyingToContainer").addClass("d-none");
    }

    function setReplyState(commentId, username) {
        replyingToCommentId = commentId;
        replyingToUsername = username;
        $("#replyingToUsername").text("@" + username);
        $("#replyingToContainer").removeClass("d-none");
    }

    function deleteComment(commentId, blogId) {
        return $.ajax({
            url: baseUrl + "/api/comments/delete.php",
            method: "POST",
            dataType: "json",
            data: { id: commentId }
        })
        .done(function (res) {
            if (res && res.success) {
                loadComments(blogId);
                return;
            }
            showCommentsModalAlert(res && res.message ? res.message : "Unable to delete comment.");
        })
        .fail(function (xhr) {
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            if (response && response.errors) {
                showCommentsModalAlert(response.errors.id || response.message || "Please fix the validation errors.");
                return;
            }
            showCommentsModalAlert(response && response.message ? response.message : "Unable to delete comment.");
        });
    }

    function postComment() {
        var blogId = $commentsModal.attr("data-blog-id") || $commentsModal.data("blog-id");
        var comment = $commentInput.val().trim();

        if (!blogId) {
            showCommentsModalAlert("No blog selected.");
            return;
        }
        if (!comment) {
            showCommentsModalAlert("Comment is required.");
            return;
        }
        if ($postCommentButton.data("posting")) {
            return;
        }

        $postCommentButton.data("posting", true);
        $postCommentButton.prop("disabled", true).text("Posting...");
        hideCommentsModalAlert();

        $.ajax({
            url: baseUrl + "/api/comments/create.php",
            method: "POST",
            dataType: "json",
            data: {
                blogId: blogId,
                comment: comment,
                parentId: replyingToCommentId
            }
        })
        .done(function (res) {
            if (res && res.success) {
                $commentInput.val("");
                clearReplyState();
                loadComments(blogId);
                return;
            }
            if (res && res.errors) {
                showCommentsModalAlert(res.errors.comment || res.errors.blogId || res.message || "Please fix the validation errors.");
                return;
            }
            showCommentsModalAlert(res && res.message ? res.message : "Unable to create comment.");
        })
        .fail(function (xhr) {
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            if (response && response.errors) {
                showCommentsModalAlert(response.errors.comment || response.errors.blogId || response.message || "Please fix the validation errors.");
                return;
            }
            showCommentsModalAlert(response && response.message ? response.message : "Unable to create comment.");
        })
        .always(function () {
            $postCommentButton.data("posting", false);
            $postCommentButton.prop("disabled", false).text("Post");
        });
    }

    function loadComments(blogId) {
        var $commentsList = $("#commentsList");
        $commentsList.empty().append($("<div>").addClass("text-muted").text("Loading comments..."));

        $.ajax({
            url: baseUrl + "/api/comments/list.php",
            method: "GET",
            dataType: "json",
            data: { blogId: blogId }
        })
        .done(function (res) {
            if (res && res.success) {
                var comments = res.data && res.data.comments ? res.data.comments : [];
                var count = res.data && typeof res.data.count !== "undefined" ? res.data.count : comments.length;

                updateCommentsCount(count);
                if (typeof updateBlogCommentCount === "function") {
                    updateBlogCommentCount(blogId, count);
                }
                renderComments(comments);
                return;
            }
            showCommentsEmptyState(res && res.message ? res.message : "Unable to load comments.");
        })
        .fail(function (xhr) {
            var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Unable to load comments.";
            showCommentsEmptyState(message);
        });
    }

    function openCommentModal(blogId, title) {
        if (!blogId) return;

        var $commentsModal = $("#commentsModal");
        if (!$commentsModal.length) return;

        $commentsModal.attr("data-blog-id", blogId).data("blog-id", blogId);
        $("#commentsModalBlogTitle").text(title || "Blog post");
        $("#commentsList").empty().append($("<div>").addClass("text-muted").text("Loading comments..."));
        bootstrap.Modal.getOrCreateInstance($commentsModal[0]).show();
    }

    var $commentsModal = $("#commentsModal");
    var $commentInput = $("#commentInput");
    var $postCommentButton = $("#postCommentButton");
    var replyingToCommentId = null;
    var replyingToUsername = null;

    window.BlogSphere = window.BlogSphere || {};
    window.BlogSphere.openCommentModal = openCommentModal;
    window.openCommentModal = openCommentModal;

    $commentsModal.on("shown.bs.modal", function () {
        hideCommentsModalAlert();
        $commentInput.val("");
        var blogId = $(this).attr("data-blog-id") || $(this).data("blog-id");
        if (!blogId) {
            showCommentsEmptyState("No blog selected.");
            return;
        }
        loadComments(blogId);
    });

    $commentsModal.on("hidden.bs.modal", function () {
        $commentInput.val("");
        hideCommentsModalAlert();
        clearReplyState();
        $("#commentsList").empty();
        $("#commentsCount").text("0 comments");
        $(this).removeAttr("data-blog-id").removeData("blog-id");
    });

    $postCommentButton.on("click", function () {
        postComment();
    });

    $commentInput.on("keydown", function (event) {
        if (event.key !== "Enter" || event.shiftKey) return;
        event.preventDefault();
        postComment();
    });

    $commentsModal.on("click", ".comment-delete-button", function () {
        var $button = $(this);
        var commentId = $button.data("comment-id");
        var blogId = $button.data("blog-id") || $commentsModal.attr("data-blog-id") || $commentsModal.data("blog-id");

        if (!commentId || !blogId) return;

        var $confirmModal = $("#deleteCommentConfirmModal");
        $confirmModal.data("comment-id", commentId).data("blog-id", blogId);
        bootstrap.Modal.getOrCreateInstance($confirmModal[0]).show();
    });

    $(document).on("click", "#confirmDeleteCommentButton", function () {
        var $confirmModal = $("#deleteCommentConfirmModal");
        var commentId = $confirmModal.data("comment-id");
        var blogId = $confirmModal.data("blog-id");
        var $confirmBtn = $(this);

        if (!commentId || !blogId) return;

        $confirmBtn.prop("disabled", true).text("Deleting...");
        hideCommentsModalAlert();

        deleteComment(commentId, blogId).always(function () {
            $confirmBtn.prop("disabled", false).text("Delete");
            bootstrap.Modal.getOrCreateInstance($confirmModal[0]).hide();
        });
    });

    $commentsModal.on("click", ".comment-reply-button", function () {
        var commentId = $(this).data("comment-id");
        var username = $(this).data("username");

        if (!commentId) return;

        setReplyState(commentId, username);
        $commentInput.val("@" + username + " ").focus();
    });

    $commentsModal.on("click", "#cancelReplyButton", function () {
        clearReplyState();
        $commentInput.val("").focus();
    });

    function setLikeState(button, likeCount, isLiked) {
        var $button = $(button);
        var $icon   = $button.find(".iconify");

        $button.attr("data-like-count", String(likeCount));
        $button.attr("data-liked", isLiked ? "1" : "0");
        $button.find(".action-count").text(String(likeCount));

        if (isLiked) {
            $button.addClass("is-active btn-outline-primary").removeClass("btn-outline-secondary");
            if ($icon.length) { $icon.attr("data-icon", "mdi:heart"); }
        } else {
            $button.removeClass("is-active btn-outline-primary").addClass("btn-outline-secondary");
            if ($icon.length) { $icon.attr("data-icon", "mdi:heart-outline"); }
        }
    }

    function setShareState(button, shareCount) {
        var $button = $(button);
        $button.attr("data-share-count", String(shareCount));
        $button.find(".action-count").text(String(shareCount));
    }

    function persistShare(button) {
        var $button = $(button);
        var blogId  = $button.data("blog-id");
        if (!blogId) { return; }

        $.ajax({
            url:      baseUrl + "/api/blogs/share",
            method:   "POST",
            dataType: "json",
            data:     { blogId: blogId }
        })
        .done(function (res) {
            if (res && res.success) {
                var shareCount = res.data && typeof res.data.sharesCount !== "undefined"
                    ? res.data.sharesCount
                    : (parseInt($button.attr("data-share-count"), 10) || 0) + 1;
                setShareState(button, shareCount);
                return;
            }
            alert(res && res.message ? res.message : "Unable to record this share.");
        })
        .fail(function (xhr) {
            var message = xhr && xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : "Unable to record this share.";
            alert(message);
        });
    }

    function copyShareLink(shareUrl) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(shareUrl);
        }

        return new Promise(function (resolve, reject) {
            var $temp = $("<textarea>")
                .css({ position: "fixed", left: "-9999px", top: 0 })
                .val(shareUrl)
                .appendTo("body");

            $temp[0].select();
            try {
                document.execCommand("copy");
                resolve();
            } catch (err) {
                reject(err);
            } finally {
                $temp.remove();
            }
        });
    }

    function handleShare(button) {
        var $button  = $(button);
        var title    = $button.data("title")     || "Blog post";
        var shareUrl = $button.data("share-url") || window.location.href;

        if ($button.data("requestPending")) { return; }

        $button.data("requestPending", true);
        $button.prop("disabled", true);

        function releaseShareButton() {
            $button.data("requestPending", false);
            $button.prop("disabled", false);
        }

        if (navigator.share) {
            navigator.share({
                title: title,
                text:  "BlogSphere post",
                url:   shareUrl
            })
            .then(function () {
                persistShare(button);
            })
            .catch(function (error) {
                if (error && error.name !== "AbortError") {
                    alert("Unable to share this blog.");
                }
            })
            .finally(releaseShareButton);

            return;
        }

        copyShareLink(shareUrl)
            .then(function () {
                alert("Link copied to clipboard.");
                persistShare(button);
            })
            .catch(function () {
                alert("Unable to copy link. Please copy manually:\n" + shareUrl);
            })
            .finally(releaseShareButton);
    }

    $(document).on("click", ".action-button", function (event) {
        event.preventDefault();
        event.stopPropagation();

        var $button       = $(this);
        var action        = $button.data("action");
        var requiresLogin = $button.attr("data-requires-login") === "1";
        var isLoggedIn = $("body").attr("data-is-logged-in") === "1";

        if (requiresLogin && !isLoggedIn) {
            if (window.BlogSphere && typeof window.BlogSphere.showGuestModal === "function") {
                window.BlogSphere.showGuestModal();
            }
            return;
        }

        if (action === "like") {
            if ($button.data("requestPending")) { return; }

            var id = $button.data("blog-id");
            if (!id) { return; }

            $button.data("requestPending", true);
            $button.prop("disabled", true);

            $.ajax({
                url:      baseUrl + "/api/blogs/like",
                method:   "POST",
                dataType: "json",
                data:     { blogId: id }
            })
            .done(function (res) {
                if (res && res.success) {
                    var likeCount = res.data && typeof res.data.likesCount !== "undefined"
                        ? res.data.likesCount
                        : parseInt($button.attr("data-like-count"), 10) || 0;
                    var liked = !!(res.data && res.data.liked);
                    setLikeState($button[0], likeCount, liked);
                    return;
                }
                alert(res && res.message ? res.message : "Unable to like this blog.");
            })
            .fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : "Unable to like this blog.";
                alert(message);
            })
            .always(function () {
                $button.data("requestPending", false);
                $button.prop("disabled", false);
            });
            return;
        }

        if (action === "comment") {
            var commentBlogId = $button.data("blog-id");
            var blogTitleText = $button.data("title") || "Blog post";

            if (!commentBlogId) { return; }
            if (typeof openCommentModal === "function") {
                openCommentModal(commentBlogId, blogTitleText);
            }
            return;
        }

        if (action === "share") {
            handleShare(this);
            return;
        }
    });

    var $footerAvatarContainer = $("#currentUserCommentAvatar");
    if ($footerAvatarContainer.length) {
        var $footerAvatar = renderCommentAvatar(currentUser, 38);
        $footerAvatarContainer.empty().append($footerAvatar);
    }
});
</script>
';

?>

<div
    class="modal fade"
    id="commentsModal"
    tabindex="-1"
    aria-labelledby="commentsModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <div class="d-flex flex-column gap-0">

                    <h5 class="modal-title mb-0" id="commentsModalLabel">
                        Comments
                    </h5>

                    <small id="commentsModalBlogTitle" class="text-muted">
                        Blog title placeholder
                    </small>

                    <small id="commentsCount" class="text-muted d-block">
                        0 comments
                    </small>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <div class="modal-body d-flex flex-column gap-3" style="min-height: 0;">

                <div id="commentsModalAlert" class="alert d-none mb-3" role="alert"></div>

                <div id="commentsList" class="flex-grow-1 py-1" style="min-height: 250px; overflow-y: auto; max-height: 460px;">

                    <div class="text-muted">
                        Loading comments...
                    </div>

                </div>

            </div>

            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <div class="d-flex gap-3 align-items-start w-100">
                    <!-- Active user avatar -->
                    <div id="currentUserCommentAvatar" class="flex-shrink-0 mt-1">
                        <!-- Populated dynamically by JS -->
                    </div>
                    
                    <!-- Input Area -->
                    <div class="flex-grow-1 min-w-0 d-flex flex-column gap-2">
                        <!-- Replying to banner -->
                        <div id="replyingToContainer" class="d-none alert alert-light border py-2 px-3 mb-0 rounded-3 d-flex align-items-center justify-content-between small text-muted">
                            <div>
                                Replying to <span id="replyingToUsername" class="fw-bold text-dark"></span>
                            </div>
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none fw-semibold" id="cancelReplyButton">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        
                        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-end">
                            <div class="flex-grow-1">
                                <textarea
                                    id="commentInput"
                                    class="form-control border-0 bg-light py-2.5 px-3"
                                    rows="2"
                                    style="min-height: 52px; max-height: 120px; border-radius: 12px; font-size: 0.92rem; box-shadow: none; resize: none;"
                                    placeholder="Write a comment..."
                                ></textarea>
                            </div>
                            <button
                                type="button"
                                class="btn btn-dark px-4 fw-semibold rounded-3 text-white flex-shrink-0"
                                id="postCommentButton"
                                style="height: 42px;"
                            >
                                Post
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Delete Comment Confirmation Modal -->
<div class="modal fade" id="deleteCommentConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg rounded-4" style="padding: 0 !important;">
            <div class="modal-header border-0 pb-0 pt-4 px-4" style="padding: 24px 24px 8px !important;">
                <h5 class="modal-title fw-bold" style="font-size: 1.20rem; letter-spacing: -0.01em; font-weight: 700 !important;">Delete comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3 px-4" style="padding: 8px 24px 20px !important;">
                <p class="mb-0 text-muted" style="font-size: 0.92rem; color: #555 !important;">Are you sure you want to delete this comment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2" style="padding: 12px 24px 24px !important;">
                <button type="button" class="btn btn-outline-dark px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.85rem; border-radius: 10px; padding: 9px 20px !important; background-color: transparent !important; border: 1.5px solid #e0e0e0 !important; color: #111111 !important;">Cancel</button>
                <button type="button" class="btn btn-danger px-3 py-2 fw-semibold" id="confirmDeleteCommentButton" style="font-size: 0.85rem; border-radius: 10px; padding: 9px 20px !important; background-color: #dc3545 !important; border: none !important; color: #ffffff !important;">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styling for modern premium comments modal */
#commentsModal .modal-content {
    border: none !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12) !important;
}
#commentsModal .modal-header {
    border-bottom: 1px solid #f0f2f5 !important;
}
#commentsModal .modal-body {
    padding-top: 20px !important;
}
#commentsList::-webkit-scrollbar {
    width: 6px;
}
#commentsList::-webkit-scrollbar-track {
    background: transparent;
}
#commentsList::-webkit-scrollbar-thumb {
    background: #e4e6eb;
    border-radius: 10px;
}
#commentsList::-webkit-scrollbar-thumb:hover {
    background: #ccd0d5;
}
.comment-action-link {
    font-size: 0.78rem;
    font-weight: 700;
    color: #65676b;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    text-decoration: none;
    transition: color 0.15s ease;
}
.comment-action-link:hover {
    color: #050505;
}
.comment-action-link.delete-link {
    color: #dc3545;
}
.comment-action-link.delete-link:hover {
    color: #a71d2a;
}
#commentInput:focus {
    background-color: #f0f2f5 !important;
    box-shadow: 0 0 0 2px #000 !important;
}
</style>
