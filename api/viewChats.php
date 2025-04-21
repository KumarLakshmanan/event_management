<?php
session_start();
include("./config.php");

$db = new Connection();
$conn = $db->getConnection();
if (isset($_REQUEST["id"])) {
    try {
        $id = $_REQUEST["id"];
        $sql = "SELECT * FROM chat_history WHERE chat_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetchAll();
?>
        <table class="table table-striped">
            <?php
            for ($i = 0; $i < count($result); $i++) {
            ?>

                <tr>
                    <td><b>USER</b></td>
                    <td>:</td>
                    <td style="white-space: pre-wrap;"><?php echo $result[$i]["user_message"]; ?></td>
                </tr>
                <tr>
                    <td><b>BOT</b></td>
                    <td>:</td>
                    <td style="white-space: pre-wrap;"><?php echo $result[$i]["reply_message"]; ?></td>
                </tr>
            <?php
            }
            ?>
        </table>
<?php
    } catch (Exception $e) {
        $json["error"] = array("code" => "#500", "description" => $e->getMessage());
    }
}
