<?php

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$baseUrlJs = json_encode($baseUrl);

$pageScripts = ($pageScripts ?? '') . '
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    const baseUrl = ' . $baseUrlJs . ';

    function renderCommentAvatar(user) {
        var avatar = user && user.avatar ? user.avatar : "";
        var name = user && user.name ? user.name : "User";
        var initial = name.charAt(0).toUpperCase();

        if (avatar) {
            return $("<img>")
                .attr("src", avatar)
                .attr("alt", name)
                .addClass("rounded-circle flex-shrink-0")
                .css({ width: "44px", height: "44px", objectFit: "cover" });
        }

        return $("<div>")
            .addClass("rounded-circle bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-semibold")
            .css({ width: "44px", height: "44px" })
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
        var indent = Math.min(level, 4) * 40;

        var $item = $("<div>")
            .addClass("d-flex gap-3 py-3 align-items-start")
            .css({ marginLeft: indent + "px" });

        if (level > 0) {
            $item.addClass("border-start ps-3");
        }

        $item.append(
            createProfileLink(user, "flex-shrink-0 text-decoration-none", function () {
                var $avatar = renderCommentAvatar(user);
                if ($avatar.is("img")) {
                    $avatar.attr("loading", "lazy");
                }
                return $avatar;
            })
        );

        var $content = $("<div>").addClass("flex-grow-1 min-w-0");
        var $title = $("<div>").addClass("d-flex flex-column gap-0 mb-1");
        var $name = createProfileLink(user, "fw-semibold text-body text-decoration-none").text(user.name || "User");
        var $metaLine = $("<div>").addClass("d-flex flex-wrap align-items-center gap-1 text-muted small");

        $metaLine.append(
            createProfileLink(user, "text-muted text-decoration-none").text("@" + (user.username || "unknown")),
            $("<span>").html("&bull;"),
            $("<span>").text(formatCommentTimestamp(comment.created_at))
        );

        $title.append($name, $metaLine);
        $content.append($title);
        $content.append($("<div>").addClass("text-body mb-2").text(comment.comment || ""));

        var $actions = $("<div>").addClass("mt-1 d-flex align-items-center gap-3");
        $actions.append(renderCommentReplyButton(comment));

        var $deleteButton = renderCommentDeleteButton(comment, blogId);
        if ($deleteButton) {
            $actions.append($deleteButton);
        }

        $content.append($actions);
        $item.append($content);
        $("#commentsList").append($item);

        if (comment.replies && comment.replies.length) {
            comment.replies.forEach(function (reply) {
                renderCommentNode(reply, blogId, level + 1);
            });
        }
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
            renderCommentNode(comment, blogId, 0);
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
        if (!window.confirm("Delete this comment?")) return;

        $button.prop("disabled", true);
        hideCommentsModalAlert();

        deleteComment(commentId, blogId).always(function () {
            $button.prop("disabled", false);
        });
    });

    $commentsModal.on("click", ".comment-reply-button", function () {
        var commentId = $(this).data("comment-id");
        var username = $(this).data("username");

        if (!commentId) return;

        setReplyState(commentId, username);
        $commentInput.val("@" + username + " ").focus();
    });

    $commentsModal.on("mouseenter", ".comment-delete-button", function () {
        $(this).removeClass("text-muted").addClass("text-danger");
    });

    $commentsModal.on("mouseleave", ".comment-delete-button", function () {
        $(this).removeClass("text-danger").addClass("text-muted");
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

                <div id="commentsList" class="border rounded-3 p-3 bg-light flex-grow-1" style="min-height: 220px; overflow-y: auto;">

                    <div class="text-muted">
                        Loading comments...
                    </div>

                </div>

            </div>

            <div class="modal-footer d-flex flex-column align-items-stretch gap-3">
            <div
                id="replyingToContainer"
                class="d-none small text-muted"
            >
                Replying to
                <span id="replyingToUsername"></span>

                <button
                    type="button"
                    class="btn btn-link btn-sm p-0 ms-2"
                    id="cancelReplyButton"
                >
                    Cancel
                </button>
            </div>
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-end w-100">

                    <div class="flex-grow-1">

                        <textarea
                            id="commentInput"
                            class="form-control"
                            rows="2"
                            style="min-height: 72px; max-height: 80px;"
                            placeholder="Write a comment..."
                        ></textarea>

                    </div>

                    <button
                        type="button"
                        class="btn btn-primary flex-shrink-0"
                        id="postCommentButton"
                    >
                        Post
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>
