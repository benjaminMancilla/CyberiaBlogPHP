<?php if ($errors): ?>
    <div class="error box comment-margin">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<h3 class="form-title">Add your comment</h3>

<form
    action="view-post.php?action=add-comment&amp;post_id=<?php echo $postId?>"
    method="post"
    class="comment-form user-form"
>

    <div class="form-group">
        <label for="comment-name" class="form-label">
            Name:
        </label>
        <input
            type="text"
            id="comment-name"
            name="comment-name"
            class="form-input"
            value="<?php echo htmlEscape($commentData['user_name']) ?>"
        />
    </div>

    <div class="form-group">
        <label for="comment-website" class="form-label">
            Website:
        </label>
        <input
            type="text"
            id="comment-website"
            name="comment-website"
            class="form-input"
            value="<?php echo htmlEscape($commentData['website']) ?>"
        />
    </div>

    <div class="form-group">
        <label for="comment-text" class="form-label">
            Comment:
        </label>
        <textarea
            id="comment-text"
            name="comment-text"
            class="form-textarea"
            rows="8"
            cols="70"
        ><?php echo htmlEscape($commentData['body']) ?></textarea>
    </div>

    <div class="form-group">
        <input type="submit" value="Submit comment" class="form-submit-button" name="add-comment" />
    </div>
</form>
