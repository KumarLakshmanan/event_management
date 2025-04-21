<?php
session_start();
include("./config.php");

$db = new Connection();
$conn = $db->getConnection();
if (isset($_REQUEST["id"])) {
    try {
        $id = $_REQUEST["id"];
        $sql = "SELECT * FROM contact_forms WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch();
?>
        <table class="table table-striped">
            <tr>
                <td>Name</td>
                <td>:</td>
                <td><?php echo $result["name"]; ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td><?php echo $result["email"]; ?></td>
            </tr>
            <tr>
                <td>Contact No</td>
                <td>:</td>
                <td><?php echo $result["phone"]; ?></td>
            </tr>
            <tr>
                <td>Subject</td>
                <td>:</td>
                <td><?php echo $result["subject"]; ?></td>
            </tr>
            <tr>
                <td>Message</td>
                <td>:</td>
                <td><?php echo $result["message"]; ?></td>
            </tr>
            <tr>
                <td>Submitted At</td>
                <td>:</td>
                <td><?php echo date('d M h:i A', strtotime($result['created_at'])); ?></td>
            </tr>
        </table>
        <?php
        if ($result["status"] == "unread") {
        ?>
            <button class="btn m-2 btn-primary" onclick="markAsRead(<?php echo $result["id"]; ?>)">Mark As Read</button>
        <?php
        } else {
        ?>
            <button class="btn m-2 btn-success" onclick="markAsUnread(<?php echo $result["id"]; ?>)">Mark As Unread</button>
        <?php
        }
        ?>
        <button class="btn m-2 btn-danger" onclick="deleteMsg(<?php echo $result["id"]; ?>)">Delete</button>
        <script>
            function markAsRead(id) {
                $.ajax({
                    url: "api/v1.php",
                    type: "POST",
                    data: {
                        id: id,
                        mode: "closeMessage"
                    },
                    success: function(data) {
                        if (data.error.code == "#200") {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Message marked as read",
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                               location.reload()
                            });
                        } else {
                            alert("Something went wrong");
                        }
                    }
                });
            }

            function markAsUnread(id) {
                $.ajax({
                    url: "api/v1.php",
                    type: "POST",
                    data: {
                        id: id,
                        mode: "openMessage"
                    },
                    success: function(data) {
                        if (data.error.code == "#200") {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Message marked as unread",
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                               location.reload()
                            });
                        } else {
                            alert("Something went wrong");
                        }
                    }
                });
            }

            function deleteMsg(id) {
                $.ajax({
                    url: "api/v1.php",
                    type: "POST",
                    data: {
                        id: id,
                        mode: "deleteMessage"
                    },
                    success: function(data) {
                        if (data.error.code == "#200") {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Message deleted successfully",
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                               location.reload()
                            });
                        } else {
                            alert("Something went wrong");
                        }
                    }
                });
            }
        </script>
<?php
    } catch (Exception $e) {
        $json["error"] = array("code" => "#500", "description" => $e->getMessage());
    }
}
