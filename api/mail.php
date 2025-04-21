<?php

function sendFirstMessage($chatId, $message)
{
    $mailTemplate = '<table class="m_6759670858515357264align_center" style="
                        border-spacing: 0;
                        border-collapse: separate;
                        table-layout: fixed;
                        margin: 0 auto;">
                    <tbody>';
    for ($i = 0; $i < count($message); $i++) {
        $mailTemplate .=    '<tr>
                                <td style="font-family: Helvetica, Arial, sans-serif; padding: 0">
                                    <table class="m_6759670858515357264message_thread" style="
                                                border-spacing: 0;
                                                border-collapse: separate;
                                                table-layout: fixed;
                                                font-size: 14px;
                                                max-width: 344px;
                                                text-align: center;
                                                width: 100%;
                                                ">
                                        <tbody>
                                            <tr>
                                                <td style="
                                                        font-family: Helvetica, Arial, sans-serif;
                                                        padding: 0;
                                                        display: flex;
                                                        justify-content: end;
                                                    ">
                                                    <table class="m_6759670858515357264comment" style="
                                                        border-spacing: 0;
                                                        border-collapse: separate;
                                                        table-layout: fixed;
                                                        margin-bottom: 16px;
                                                        text-align: left;
                                                        ">
                                                        <tbody>
                                                            <tr>
                                                                <td class="m_6759670858515357264part_body m_6759670858515357264admin_part" style="
                                                                font-family: Helvetica, Arial, sans-serif;
                                                                border-radius: 12px;
                                                                text-align: left;
                                                                padding: 0;
                                                                background: #286efa;
                                                            " align="left">
                                                                    <table style="
                                                                border-spacing: 0;
                                                                border-collapse: separate;
                                                                table-layout: fixed;
                                                                ">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="m_6759670858515357264rendered_comment" style="
                                                                        font-family: Helvetica, Arial,
                                                                        sans-serif;
                                                                        padding: 12px 16px;
                                                                    ">
                                                                                    <div>
                                                                                        <p class="m_6759670858515357264no-margin" style="line-height: 20px;color: white;margin: 0;white-space:pre-wrap;">' . $message[$i]['user_message'] . '</p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-family: Helvetica, Arial, sans-serif; padding: 0">
                                    <table class="m_6759670858515357264message_thread" style="
                                    border-spacing: 0;
                                    border-collapse: separate;
                                    table-layout: fixed;
                                    font-size: 14px;
                                    max-width: 344px;
                                    text-align: center;
                                    width: 100%;
                                    ">
                                        <tbody>
                                            <tr>
                                                <td style="
                                            font-family: Helvetica, Arial, sans-serif;
                                            padding: 0;
                                        ">
                                                    <p class="m_6759670858515357264user_greeting" style="line-height: 20px; margin: 0 0 8px"></p>
                                                </td>
                                            </tr>
                        
                                            <tr>
                                                <td style="
                                            font-family: Helvetica, Arial, sans-serif;
                                            padding: 0;
                                        ">
                                                    <table class="m_6759670858515357264comment" style="
                                            border-spacing: 0;
                                            border-collapse: separate;
                                            table-layout: fixed;
                                            margin-bottom: 16px;
                                            ">
                                                        <tbody>
                                                            <tr>
                                                                <td class="m_6759670858515357264part_body m_6759670858515357264admin_part" style="
                                                    font-family: Helvetica, Arial, sans-serif;
                                                    border-radius: 12px;
                                                    text-align: left;
                                                    background-color: #f1f1f1;
                                                    padding: 0;
                                                " align="left" bgcolor="#F1F1F1">
                                                                    <table style="
                                                    border-spacing: 0;
                                                    border-collapse: separate;
                                                    table-layout: fixed;
                                                    ">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="m_6759670858515357264rendered_comment" style="
                                                            font-family: Helvetica, Arial,
                                                            sans-serif;
                                                            padding: 12px 16px;
                                                        ">
                                                                                    <div>
                                                                                        <p class="m_6759670858515357264no-margin" style="line-height: 20px; margin: 0;;white-space:pre-wrap;">' . $message[$i]['reply_message'] . '</p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>';
    }
    $mailTemplate .= '<tr>
                        <td style="font-family: Helvetica, Arial, sans-serif; padding: 0">
                            <table style="
                            max-width: 344px;
                            width: 100%;
                            border-spacing: 0;
                            border-collapse: separate;
                            table-layout: fixed;
                            ">
                                <tbody>
                                    <tr style="text-align: center" align="center">
                                        <td class="m_6759670858515357264content-td" style="
                                    font-family: Helvetica, Arial, sans-serif;
                                    line-height: 20px;
                                    font-size: 14px;
                                    padding: 0;
                                ">
                                            <a class="m_6759670858515357264reply-in-messenger-button" style="
                                    background-color: #286efa;
                                    outline: none !important;
                                    color: white;
                                    text-decoration: none;
                                    border-radius: 20px;
                                    display: inline-block;
                                    font-size: 12px;
                                    margin: 0px 0 16px;
                                    padding: 6px 25px;
                                    border-style: none;
                                    " href="https://dev.codingfrontend.in/zar/admin/chatmessages" target="_blank">Go to Admin Panel</a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="m_6759670858515357264content-td" style="
                                    font-family: Helvetica, Arial, sans-serif;
                                    line-height: 20px;
                                    font-size: 14px;
                                    padding: 0;
                                ">
                                            <p class="m_6759670858515357264reply-in-messenger-message" style="
                                    font-size: 10px;
                                    line-height: 16px;
                                    padding-top: 0;
                                    color: #8c8c8c;
                                    margin: 0;
                                    ">
                                                Chat ID: ' . $chatId . '
                                            </p>
                                            <p class="m_6759670858515357264reply-in-messenger-message" style="
                                    font-size: 10px;
                                    line-height: 16px;
                                    padding-top: 0;
                                    color: #8c8c8c;
                                    margin: 0;
                                    ">
                                                You may need to sign in to Customer Service again. Click the button above
                                                to sign in.
                                            </p>
                                        </td>
                                    </tr>

                                    <tr height="16"></tr>
                                    <tr>
                                        <td style="
                                    font-family: Helvetica, Arial, sans-serif;
                                    padding: 0;
                                ">
                                            <hr class="m_6759670858515357264divider" style="
                                    height: 1px;
                                    color: #f0f0f0;
                                    background-color: #f0f0f0;
                                    margin: 0;
                                    border-style: none;
                                    " />
                                        </td>
                                    </tr>
                                    <tr height="16"></tr>
                                    <tr>
                                        <td valign="top" id="m_6759670858515357264powered_by_badge" class="m_6759670858515357264deemphasized_text m_6759670858515357264powered_by_badge" style="
                                    font-size: 10px;
                                    line-height: 12px;
                                    font-family: Helvetica, Arial, sans-serif;
                                    color: #8c8c8c;
                                    padding: 0;
                                ">
                                            by IWD
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
                </table>';

    $tomail = "srivelmurugantransportmadurai@gmail.com";
    $subject = "New Message from Customer Chat Support";
    $frommail = "support@sriveltransport.com";
    $headers = "From: " . $frommail . "\r\n";
    $headers .= "Reply-To: " . $frommail . "\r\n";
    $headers .= "Content-type: text/html\r\n";
    $ccmail = "veeramaniselvaraj369@gmail.com,lakshmanan.coder@gmail.com";
    $headers .= "Cc: " . $ccmail . "\r\n";
    return mail($tomail, $subject, $mailTemplate, $headers);
}


function sendContactMessage($name, $email, $phone, $subject, $message)
{
    $mailTemplate = '<div style="font-family:sans-serif;font-size:14px;display:block;margin:0 auto;max-width:580px;padding:10px;width:100%">
        <h2 style="font-size:23px;font-weight:bold">Hi Admin,</h2>
        <h4 style="font-size:18px;font-weight:bold">New Contact Form Submitted from the webpage</h4>
        <table style="width:100%">
            <tbody><tr>
                <td style="width:20%">Customer Name</td>
                <td>:</td>
                <td style="width:80%">' . $name . '</td>
            </tr>
            <tr>
                <td style="width:20%">Customer Phone</td>
                <td>:</td>
                <td style="width:80%"><a href="tel:' . $phone . '" target="_blank">' . $phone . '</a></td>
            </tr>
            <tr>
                <td style="width:20%">Customer Email</td>
                <td>:</td>
                <td style="width:80%"><a href="mailto:' . $email . '" target="_blank">' . $email . '</a></td>
            </tr>
            <tr>
                <td style="width:20%">Customer Subject</td>
                <td>:</td>
                <td style="width:80%">' . $subject . '</td>
            </tr>
            <tr>
                <td style="width:20%">Message</td>
                <td>:</td>
                <td style="width:80%">' . $message . '</td>
            </tr>
        </tbody></table><h3>Quick Actions</h3>
        <a style="display:inline-block;color:#ffffff;background-color:#3498db;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;text-decoration:none;font-size:14px;font-weight:bold;margin:5px;padding:10px;text-transform:capitalize;border-color:#3498db" href="tel:' . $phone . '" target="_blank">Call Now</a>
        <a style="display:inline-block;color:#ffffff;background-color:#3498db;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;text-decoration:none;font-size:14px;font-weight:bold;margin:5px;padding:10px;text-transform:capitalize;border-color:#3498db" href="mailto:' . $email . '" target="_blank">Send Email</a>
        <a style="display:inline-block;color:#ffffff;background-color:#3498db;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;text-decoration:none;font-size:14px;font-weight:bold;margin:5px;padding:10px;text-transform:capitalize;border-color:#3498db" href="https://dev.codingfrontend.in/zar/admin/" target="_blank">Admin Panel</a>
    </div>';

    $tomail = "srivelmurugantransportmadurai@gmail.com";
    $subject = "New Message from Customer Contact Form";
    $frommail = "support@sriveltransport.com";
    $headers = "From: " . $frommail . "\r\n";
    $headers .= "Reply-To: " . $frommail . "\r\n";
    $headers .= "Content-type: text/html\r\n";
    $ccmail = "veeramaniselvaraj369@gmail.com,lakshmanan.coder@gmail.com";
    $headers .= "Cc: " . $ccmail . "\r\n";

    return mail($tomail, $subject, $mailTemplate, $headers);
}


function sendEnquiryForm($name, $email, $phone, $youarea, $bustype, $noofbus, $fromdate, $todate, $message)
{
    $mailTemplate = '<div style="font-family:sans-serif;font-size:14px;display:block;margin:0 auto;max-width:580px;padding:10px;width:100%">
        <h2 style="font-size:23px;font-weight:bold">Hi Admin,</h2>
        <h4 style="font-size:18px;font-weight:bold">New Enquiry Form Submitted from the webpage</h4>
        <table style="width:100%">
            <tbody><tr>
                <td style="width:20%">Customer Name</td>
                <td>:</td>
                <td style="width:80%">' . $name . '</td>
            </tr>
            <tr>
                <td style="width:20%">Customer Phone</td>
                <td>:</td>
                <td style="width:80%"><a href="tel:' . $phone . '" target="_blank">' . $phone . '</a></td>
            </tr>
            <tr>
                <td style="width:20%">Customer Email</td>
                <td>:</td>
                <td style="width:80%"><a href="mailto:' . $email . '" target="_blank">' . $email . '</a></td>
            </tr>
            <tr>
                <td style="width:20%">You are a</td>
                <td>:</td>
                <td style="width:80%">' . $youarea . '</td>
            </tr>
            <tr>
                <td style="width:20%">Bus Type</td>
                <td>:</td>
                <td style="width:80%">' . $bustype . '</td>
            </tr>
            <tr>
                <td style="width:20%">No of Buses</td>
                <td>:</td>
                <td style="width:80%">' . $noofbus . '</td>
            </tr>
            <tr>
                <td style="width:20%">From Date</td>
                <td>:</td>
                <td style="width:80%">' . $fromdate . '</td>
            </tr>
            <tr>
                <td style="width:20%">To Date</td>
                <td>:</td>
                <td style="width:80%">' . $todate . '</td>
            </tr>
            <tr>
                <td style="width:20%">Message</td>
                <td>:</td>
                <td style="width:80%">' . $message . '</td>
            </tr>
        </tbody></table><h3>Quick Actions</h3>
        <a style="display:inline-block;color:#ffffff;background-color:#3498db;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;text-decoration:none;font-size:14px;font-weight:bold;margin:5px;padding:10px;text-transform:capitalize;border-color:#3498db" href="tel:' . $phone . '" target="_blank">Call Now</a>
        <a style="display:inline-block;color:#ffffff;background-color:#3498db;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;text-decoration:none;font-size:14px;font-weight:bold;margin:5px;padding:10px;text-transform:capitalize;border-color:#3498db" href="mailto:' . $email . '" target="_blank">Send Email</a>
        <a style="display:inline-block;color:#ffffff;background-color:#3498db;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;text-decoration:none;font-size:14px;font-weight:bold;margin:5px;padding:10px;text-transform:capitalize;border-color:#3498db" href="https://dev.codingfrontend.in/zar/admin/" target="_blank">Admin Panel</a>
    </div>';
    $tomail = "srivelmurugantransportmadurai@gmail.com";
    $subject = "New Message from Customer Booking Form";
    $frommail = "support@sriveltransport.com";
    $headers = "From: " . $frommail . "\r\n";
    $headers .= "Reply-To: " . $frommail . "\r\n";
    $headers .= "Content-type: text/html\r\n";
    $ccmail = "veeramaniselvaraj369@gmail.com,lakshmanan.coder@gmail.com";
    $headers .= "Cc: " . $ccmail . "\r\n";

    return mail($tomail, $subject, $mailTemplate, $headers);
}
