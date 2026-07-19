<?php
namespace App\Mail\Templates;

class VerifyEmail {
    public static function render(string $name, string $url): string {
        return <<<HTML
            <!DOCTYPE html>
            <html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Verify your email address</title>
                <meta name="color-scheme" content="dark">
                <meta name="supported-color-schemes" content="dark">
                <!--[if mso]>
                <noscript>
                    <xml>
                        <o:OfficeDocumentSettings>
                            <o:PixelsPerInch>96</o:PixelsPerInch>
                        </o:OfficeDocumentSettings>
                    </xml>
                </noscript>
                <![endif]-->
                <style>
                    /* Base Resets */
                    a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                    }
                </style>
            </head>
            <body style="margin: 0; padding: 0; width: 100%; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #050505; color: #f2f2f2; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

                <!-- Preheader -->
                <div style="display: none; font-size: 1px; color: #050505; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all;">
                    Verify your email to activate your Anime Nigeria account.
                </div>

                <!-- Outer Background -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #050505;">
                    <tr>
                        <td align="center" style="padding: 20px 10px;">

                            <!--[if (gte mso 9)|(IE)]>
                            <table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
                            <tr>
                            <td align="center" valign="top" width="600">
                            <![endif]-->

                            <!-- Main Card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #121214; border-radius: 12px; overflow: hidden; border: 1px solid #232326;">
                                
                                <!-- Top Accent Bar -->
                                <tr>
                                    <td style="height: 4px; background-color: #DF9A1B; font-size: 1px; line-height: 1px;">&nbsp;</td>
                                </tr>

                                <!-- Header -->
                                <tr>
                                    <td align="center" style="padding: 32px 32px 20px;">
                                        <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 800;">
                                            Anime <span style="color: #DF9A1B;">Nigeria</span>
                                        </h1>
                                        <p style="margin: 6px 0 0; color: #888888; font-size: 13px;">
                                            Connecting anime fans across Nigeria
                                        </p>
                                    </td>
                                </tr>

                                <!-- Content -->
                                <tr>
                                    <td style="padding: 0 24px 24px;">
                                        <h2 style="margin: 0 0 16px; color: #ffffff; font-size: 20px; font-weight: 700; text-align: center;">
                                            Verify your email
                                        </h2>

                                        <p style="margin: 0 0 16px; color: #a1a1aa; font-size: 15px; line-height: 1.6;">
                                            Hi <strong>{$name}</strong>,
                                        </p>

                                        <p style="margin: 0 0 24px; color: #a1a1aa; font-size: 15px; line-height: 1.6;">
                                            Thank you for creating your Anime Nigeria account. Before you can start exploring the community, please verify your email address to activate your account.
                                        </p>

                                        <!-- Bulletproof CTA Button -->
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 24px;">
                                            <tr>
                                                <td align="center">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td align="center" bgcolor="#DF9A1B" style="border-radius: 999px;">
                                                                <a href="{$url}" aria-label="Verify your Anime Nigeria email address" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 16px 36px; color: #000000; text-decoration: none; font-size: 16px; font-weight: 700; border-radius: 999px;">
                                                                    Verify Email Address
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <hr style="border: none; border-top: 1px solid #232326; margin: 28px 0;">

                                        <p style="margin: 0 0 24px; color: #a1a1aa; font-size: 13px; text-align: center;">
                                            This verification link will expire in <strong style="color: #ffffff; font-weight: 600;">24 hours</strong>.
                                        </p>

                                        <!-- Secondary / Fallback Link -->
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #1a1a1d; border: 1px solid #232326; border-radius: 8px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <p style="margin: 0 0 8px; color: #888888; font-size: 12px;">
                                                        If the button above doesn't work, copy and paste the following link into your web browser:
                                                    </p>
                                                    <p style="margin: 0; word-break: break-all; font-size: 12px; line-height: 1.5;">
                                                        <a href="{$url}" target="_blank" rel="noopener noreferrer" style="color: #DF9A1B; text-decoration: none;">
                                                            {$url}
                                                        </a>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Security Notice -->
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 32px; border-top: 1px solid #232326; border-bottom: 1px solid #232326;">
                                            <tr>
                                                <td align="center" style="padding: 20px 0;">
                                                    <p style="margin: 0 0 8px; color: #ffffff; font-size: 13px; font-weight: 600;">
                                                        Security Notice
                                                    </p>
                                                    <p style="margin: 0; color: #888888; font-size: 12px; line-height: 1.6; text-align: center;">
                                                        If you didn't create an Anime Nigeria account, you can safely ignore this email.<br>
                                                        Your email address won't be used unless it's successfully verified.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <!--[if (gte mso 9)|(IE)]>
                            </td>
                            </tr>
                            </table>
                            <![endif]-->

                            <!-- Footer Section -->
                            <!--[if (gte mso 9)|(IE)]>
                            <table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
                            <tr>
                            <td align="center" valign="top" width="600">
                            <![endif]-->
                            
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; margin-top: 24px;">
                                <tr>
                                    <td align="center" style="padding: 0 16px;">
                                        
                                        <p style="margin: 0 0 8px; color: #ffffff; font-size: 13px; font-weight: 600;">
                                            Need help?
                                        </p>

                                        <p style="margin: 0 0 24px; color: #a1a1aa; font-size: 12px; line-height: 1.6; text-align: center;">
                                            Visit our Help Centre or contact our support team<br>
                                            if you're having trouble verifying your account.<br>
                                            <a href="mailto:support@animenigeria.com" style="color: #DF9A1B; text-decoration: none;">support@animenigeria.com</a>
                                        </p>

                                        <p style="margin: 0 0 8px; color: #666666; font-size: 11px;">
                                            This is an automated email. Please do not reply to this message.
                                        </p>

                                        <p style="margin: 0; color: #666666; font-size: 11px;">
                                            &copy; 2026 Anime Nigeria. All rights reserved.
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <!--[if (gte mso 9)|(IE)]>
                            </td>
                            </tr>
                            </table>
                            <![endif]-->

                        </td>
                    </tr>
                </table>

            </body>
            </html>
        HTML;
    }
}