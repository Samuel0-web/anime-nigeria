<?php
namespace App\Mail\Templates;

class PasswordReset {
    public static function render(string $name, string $url): string {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="color-scheme" content="light dark">
            <meta name="supported-color-schemes" content="light dark">
            <title>Reset your password</title>
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
                /* Base resets */
                body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
                table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
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
        <body style="margin: 0; padding: 0; width: 100% !important; -webkit-font-smoothing: antialiased; background-color: #ffffff; color: #1f2937; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

            <!-- Hidden Preheader with spacer to prevent trailing text preview -->
            <div style="display: none; font-size: 1px; color: #ffffff; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
                Reset your password for your Anime Nigeria account.
                &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847, &#847,
            </div>

            <!-- Outer Table (Seamless background) -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; width: 100%;">
                <tr>
                    <td align="center" style="padding: 40px 16px;">

                        <!--[if (gte mso 9)|(IE)]>
                        <table align="center" border="0" cellspacing="0" cellpadding="0" width="540">
                        <tr>
                        <td align="center" valign="top" width="540">
                        <![endif]-->

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 540px; width: 100%; text-align: left;">
                            
                            <!-- Header / Brand -->
                            <tr>
                                <td style="padding-bottom: 24px;">
                                    <span style="font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.5px;">
                                        Anime <span style="color: #DF9A1B;">Nigeria</span>
                                    </span>
                                </td>
                            </tr>

                            <!-- Card Container -->
                            <tr>
                                <td style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 36px 32px;">
                                    
                                    <h1 style="margin: 0 0 16px; color: #111827; font-size: 20px; font-weight: 700; line-height: 1.3;">
                                        Reset your password
                                    </h1>

                                    <p style="margin: 0 0 16px; color: #4b5563; font-size: 15px; line-height: 1.6;">
                                        Hi <strong>{$name}</strong>,
                                    </p>

                                    <p style="margin: 0 0 28px; color: #4b5563; font-size: 15px; line-height: 1.6;">
                                        We received a request to reset the password for your <strong>Anime Nigeria</strong> account. Click the button below to set up a new password:
                                    </p>

                                    <!-- Bulletproof CTA Button -->
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 28px;">
                                        <tr>
                                            <td align="center" bgcolor="#DF9A1B" style="border-radius: 999px;">
                                                <a href="{$url}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 14px 28px; color: #000000; text-decoration: none; font-size: 15px; font-weight: 700; border-radius: 8px; background-color: #DF9A1B;">
                                                    Reset Password
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin: 0 0 24px; color: #6b7280; font-size: 13px; line-height: 1.5;">
                                        This password reset link is valid for <strong>10 minutes</strong>.
                                    </p>

                                    <!-- Security Notice Callout -->
                                    <div style="background-color: #f9fafb; border-left: 3px solid #DF9A1B; border-radius: 4px; padding: 14px 16px; margin-bottom: 28px;">
                                        <p style="margin: 0; color: #4b5563; font-size: 13px; line-height: 1.5;">
                                            If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged and your account stays secure.
                                        </p>
                                    </div>

                                    <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 24px 0;">

                                    <!-- Fallback Link -->
                                    <p style="margin: 0 0 8px; color: #6b7280; font-size: 12px; line-height: 1.5;">
                                        If the button above doesn't work, copy and paste this URL into your browser:
                                    </p>
                                    <p style="margin: 0; word-break: break-all; font-size: 12px; line-height: 1.5;">
                                        <a href="{$url}" target="_blank" rel="noopener noreferrer" style="color: #d97706; text-decoration: underline;">
                                            {$url}
                                        </a>
                                    </p>
                                </td>
                            </tr>

                            <!-- Minimal Footer -->
                            <tr>
                                <td style="padding-top: 28px; text-align: center;">
                                    <p style="margin: 0 0 8px; color: #9ca3af; font-size: 12px; line-height: 1.5;">
                                        Need help? Reach out at <a href="mailto:support@animenigeria.com" style="color: #6b7280; text-decoration: underline;">support@animenigeria.com</a>
                                    </p>
                                    <p style="margin: 0; color: #9ca3af; font-size: 12px; line-height: 1.5;">
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