<?php
if (!isset($conn)) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    include_once($path . "/admin/api/config.php");
    $db = new Connection();
    $conn = $db->getConnection();
}

$sql = "SELECT * FROM chat_suggestions ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();

?>

<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">Auto Replies</h3>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="suggestion_message">Suggestion Message</label>
                </div>
                <div class="col-md-6">
                    <label for="suggestion_button">Sugesstion Buttons</label>
                </div>
            </div>
            <div class="chat_suggestions">
                <?php
                for ($i = 0; $i < count($result); $i++) {
                ?>
                    <div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="suggestion_message[]" value="<?php echo $result[$i]['suggestion_message']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <textarea class="form-control" name="suggestion_button[]" rows="3"><?php echo $result[$i]['suggestion_button']; ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <button class="btn btn-danger deleteButton">Delete</button>
                        </div>
                        <hr>
                    </div>
                <?php
                }
                ?>
            </div>
            <div style="display: flex;">
                <button class="btn btn-success me-2 ml-2" id="saveButton">Save All</button>
                <button class="btn btn-primary me-2 ml-2" id="btnAddAutoReply">Add New Auto Reply</button>
            </div>
        </div>
    </div>
</div>
<script>
    $("#btnAddAutoReply").click(function() {
        $(".chat_suggestions").append(`
            <div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="suggestion_message[]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <textarea class="form-control" name="suggestion_button[]" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <button class="btn btn-danger deleteButton">Delete</button>
                </div>
                <hr>
            </div>
            `);
    });

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
                formData.append("mode", "savesuggestions");
                var messagePattern = [];
                var replyMessage = [];
                $("input[name='suggestion_message[]']").each(function() {
                    messagePattern.push($(this).val());
                });
                $("textarea[name='suggestion_button[]']").each(function() {
                    replyMessage.push($(this).val());
                });
                formData.append("suggestion_message", JSON.stringify(messagePattern));
                formData.append("suggestion_button", JSON.stringify(replyMessage));
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
    $(document).on("click", ".deleteButton", function() {
        $(this).parent().parent().remove();
    });
</script>