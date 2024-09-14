<?php if ($errors): ?>
    <div class="error box comment-margin">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<h3 class="form-title comment-toggle" id="comment-toggle">
    Add Comment
</h3>

<form
    action="view-post.php?action=add-comment&amp;post_id=<?php echo $postId?>"
    method="post"
    class="comment-form user-form"
    enctype="multipart/form-data"
    id="comment-form"
    style="display: none;"
>

    <div class="form-group" style="display: none;">
        <label for="comment-name" class="form-label">
            Name:
        </label>
        <input
            type="text"
            id="comment-name"
            name="comment-name"
            class="form-input"
            value="<?php echo isLoggedIn() ? htmlEscape(getAuthUser()) : 'anonymous' ?>"
            readonly
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
        ></textarea>
    </div>
    
    <div class="form-group button-group">
        
        <label for="comment-image" class="form-image-button">
            Upload Image
            <input
                type="file"
                id="comment-image"
                name="comment-image"
                class="form-input-file"
                accept="image/jpeg, image/png, image/gif"
            />
        </label>
        <button type="button" class="form-cancel-button">Cancel</button>
        <button type="submit" class="form-submit-button" name="add-comment">Send</button>
    </div>
</form>


