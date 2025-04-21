<?php

$csrfToken = md5(uniqid(rand(), TRUE));
if (isset($_GET['blogid'])) {
    $blogid = $_GET['blogid'];
    $sql = "SELECT * FROM blogs WHERE id = :id";
    $stmt = $pdoConn->prepare($sql);
    $stmt->bindParam(':id', $blogid);
    $stmt->execute();
    $propertyEdit = $stmt->fetch(PDO::FETCH_ASSOC);
?>
    <div class="container">
        <div class="white-box">
            <h2>Fill the Following Form To Add Blog</h2>
            <br />
            <div class="row">
                <!-- 
                    `title`, `content`, `views`, `author`, `authorthumb`, `name`, `shortcontent`, `thumbnail`, `tags`, `category`, `slug`
                -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="blogName">Blog Name</label>
                        <input type="text" class="form-control" id="blogName" placeholder="Enter Blog Name" value="<?= $propertyEdit['title'] ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="blogSlug">Blog Slug</label>
                        <input type="text" class="form-control" id="blogSlug" placeholder="Enter Blog Slug" readonly value="<?= $propertyEdit['slug'] ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="blogViews">Blog Views</label>
                        <input type="number" class="form-control" id="blogViews" placeholder="Enter Blog Views" value="<?= $propertyEdit['views'] ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="authorName">Author Name</label>
                        <input type="text" class="form-control" id="authorName" placeholder="Enter Author Name" value="<?= $propertyEdit['author'] ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="authorImage">Author Image</label>
                        <div class="input-images-1" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail</label>
                        <div class="input-images-2" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="blogTags">Blog Tags</label>
                        <input type="text" class="form-control" id="blogTags" placeholder="Enter Blog Tags" value="<?= $propertyEdit['tags'] ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="blogCategory">Blog Category</label>
                        <input type="text" class="form-control" id="blogCategory" placeholder="Enter Blog Category" value="<?= $propertyEdit['category'] ?>">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="blogCategory">Blog Description</label>
                        <textarea class="form-control" id="blogDescription" placeholder="Enter Blog Description"><?= $propertyEdit['shortcontent'] ?></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="blogContent">Blog Content</label>
                        <textarea class="form-control texteditor-content" id="blogContent" placeholder="Enter Blog Content"><?= $propertyEdit['content'] ?></textarea>
                    </div>
                </div>
            </div>
            <div class="p-2">
                <button type="button" class="w-100 btn btn-primary" id="saveButton">Save changes</button>
            </div>
        </div>
    </div>
    <link href="<?= $adminBaseUrl ?>css/image-uploader.min.css" rel="stylesheet" />
    <script src="<?= $adminBaseUrl ?>js/image-uploader.min.js"></script>
    <script>
        let _xUserData = {
            "baseURL": "<?= $baseUrl ?>",
            "auth": "<?= $_SESSION['token'] ?>",
            ".input-images-1": "<?= $propertyEdit['authorthumb'] ?>",
            ".input-images-2": "<?= $propertyEdit['thumbnail'] ?>",
        };
        $(document).ready(() => {
            imageUploader.init(".input-images-1");
            imageUploader.init(".input-images-2");
        })
        if ($(".texteditor-content").length > 0) {
            $(".texteditor-content").richText();
        }
        $("#blogName").on("input", function() {
            let blogName = $(this).val();
            // REPLACE SPECIAL CHARACTERS WITH -
            let blogSlug = blogName.toLowerCase().replace(/ /g, "-").replace(/[^\w-]+/g, "");
            $("#blogSlug").val(blogSlug);
        })

        $("#saveButton").click(function(event) {
            event.preventDefault();
            swal({
                title: 'Are you sure to save changes?',
                text: "This will be saved and pushed to the server!",
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var formData = new FormData();
                    formData.append("mode", "editblog");
                    formData.append("blog_id", "<?= $propertyEdit['id'] ?>");
                    formData.append("blog_name", $("#blogName").val());
                    formData.append("blog_slug", $("#blogSlug").val());
                    formData.append("blog_views", $("#blogViews").val());
                    formData.append("author_name", $("#authorName").val());
                    formData.append("author_image", $(".input-images-1 .uploaded-image").attr('data-name'));
                    formData.append("thumbnail", $(".input-images-2 .uploaded-image").attr('data-name'));
                    formData.append("blog_tags", $("#blogTags").val());
                    formData.append("blog_category", $("#blogCategory").val());
                    formData.append("blog_description", $("#blogDescription").val());
                    formData.append("blog_content", $(".texteditor-content").val());
                    $(".preloader").show();
                    $.ajax({
                        url: "<?= $apiUrl ?>",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(response) {
                            $(".preloader").hide();
                            if (response.error.code == '#200') {
                                swal({
                                    title: 'Success!',
                                    icon: 'success',
                                    text: "Successfully Updated!",
                                    confirmButtonText: 'Ok'
                                }).then((result) => {
                                    if (result.value) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                swal({
                                    title: 'Error!',
                                    text: response.error.description,
                                    icon: 'error',
                                    confirmButtonText: 'Try Again'
                                })
                            }
                        }
                    });
                }
            });
        });
    </script>
<?php
}
