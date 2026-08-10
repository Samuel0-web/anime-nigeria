/**
 * Static Help Centre articles.
 *
 * Each article's `content` is a list of blocks rendered dynamically by
 * HelpArticleViewer. Supported block types:
 *   - { type: "heading", text }
 *   - { type: "paragraph", text }
 *   - { type: "steps", items: string[] }       (ordered)
 *   - { type: "list", items: string[] }        (unordered)
 *   - { type: "note", text }                   (tip/callout)
 *   - { type: "link", text, href, external? }
 *
 * `section` is optional and groups articles within a category's article
 * list (see HelpCategoryView). Leave it out for a flat list.
 */
export const articles = [
    // ---- Account & Profile ------------------------------------------------
    {
        id: "change-username",
        categorySlug: "account",
        section: "Profile",
        title: "How do I change my username?",
        description: "Update your username from your profile settings.",
        tags: ["username", "handle", "profile"],
        popular: true,
        content: [
            {
                type: "paragraph",
                text: "Your username is how other members find and mention you. You can change it at any time, as long as the new one is available.",
            },
            {
                type: "steps",
                items: [
                    "Go to your Profile page from the sidebar.",
                    "Select Edit Profile.",
                    "Update the Username field.",
                    "Select Save Changes.",
                ],
            },
            {
                type: "note",
                text: "Old links to your previous username will stop working once you change it.",
            },
        ],
    },
    {
        id: "why-cant-change-username",
        categorySlug: "account",
        section: "Profile",
        title: "Why can't I change my username?",
        description: "Common reasons a username change might be blocked.",
        tags: ["username", "error", "taken"],
        popular: true,
        content: [
            {
                type: "paragraph",
                text: "If the Save button is disabled or you're seeing an error, it's usually one of the following.",
            },
            {
                type: "list",
                items: [
                    "The username is already taken by another member.",
                    "It contains characters that aren't allowed (only letters, numbers, and underscores).",
                    "It's shorter than 3 characters or longer than 20.",
                    "You've changed your username recently and are within the cooldown period.",
                ],
            },
        ],
    },
    {
        id: "edit-profile-info",
        categorySlug: "account",
        section: "Profile",
        title: "How do I edit my profile information?",
        description: "Update your bio, links, and other profile details.",
        tags: ["profile", "bio", "edit"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Open your Profile from the sidebar.",
                    "Select Edit Profile.",
                    "Update your bio, location, or links as needed.",
                    "Select Save Changes.",
                ],
            },
        ],
    },
    {
        id: "change-avatar",
        categorySlug: "account",
        section: "Profile",
        title: "How do I change my avatar?",
        description: "Upload a new profile picture.",
        tags: ["avatar", "profile picture", "image"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Open your Profile and select Edit Profile.",
                    "Select your current avatar image.",
                    "Choose a new image from your device.",
                    "Crop it if needed, then select Save.",
                ],
            },
            {
                type: "note",
                text: "If you don't upload an image, a colour avatar is generated from your username automatically.",
            },
        ],
    },
    {
        id: "change-email",
        categorySlug: "account",
        section: "Account",
        title: "How do I change my email?",
        description: "Update the email address linked to your account.",
        tags: ["email", "account"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Go to Account Settings.",
                    "Select Change Email.",
                    "Enter your new email address and current password.",
                    "Confirm the change from the verification email we send you.",
                ],
            },
        ],
    },
    {
        id: "delete-account",
        categorySlug: "account",
        section: "Account",
        title: "How do I delete my account?",
        description: "Permanently close your account.",
        tags: ["delete", "account", "close"],
        popular: false,
        content: [
            {
                type: "paragraph",
                text: "Deleting your account removes your profile, posts, and activity. This can't be undone.",
            },
            {
                type: "steps",
                items: [
                    "Go to Account Settings.",
                    "Scroll to Delete Account.",
                    "Confirm your password when prompted.",
                    "Select Permanently Delete Account.",
                ],
            },
            {
                type: "note",
                text: "Consider downloading your data first — see \"How do I download my data?\"",
            },
        ],
    },
    {
        id: "download-data",
        categorySlug: "account",
        section: "Account",
        title: "How do I download my data?",
        description: "Request a copy of your account data.",
        tags: ["data", "export", "download"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Go to Account Settings.",
                    "Select Download My Data.",
                    "We'll email you a download link once your export is ready.",
                ],
            },
            {
                type: "note",
                text: "Exports can take up to 24 hours to prepare for larger accounts.",
            },
        ],
    },

    // ---- Privacy & Security ------------------------------------------------
    {
        id: "change-password",
        categorySlug: "privacy",
        section: "Security",
        title: "How do I change my password?",
        description: "Update your password from account settings.",
        tags: ["password", "security"],
        popular: true,
        content: [
            {
                type: "steps",
                items: [
                    "Open your Profile and select Edit Profile.",
                    "Expand the Password section.",
                    "Enter your current password, then your new password.",
                    "Select Update Password.",
                ],
            },
        ],
    },
    {
        id: "two-factor",
        categorySlug: "privacy",
        section: "Security",
        title: "How do I turn on two-factor authentication?",
        description: "Add an extra layer of protection to your account.",
        tags: ["2fa", "security", "authentication"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Go to Account Settings > Security.",
                    "Select Enable Two-Factor Authentication.",
                    "Scan the QR code with an authenticator app.",
                    "Enter the 6-digit code to confirm.",
                ],
            },
        ],
    },
    {
        id: "who-can-see-profile",
        categorySlug: "privacy",
        section: "Privacy",
        title: "Who can see my profile?",
        description: "Understand your profile visibility settings.",
        tags: ["privacy", "visibility", "profile"],
        popular: false,
        content: [
            {
                type: "paragraph",
                text: "By default, your profile and public posts are visible to other members. You can restrict this from your privacy settings.",
            },
            {
                type: "list",
                items: [
                    "Public — visible to anyone, including logged-out visitors.",
                    "Members only — visible to signed-in members only.",
                    "Private — visible only to people you approve.",
                ],
            },
        ],
    },

    // ---- Posts & Media ------------------------------------------------
    {
        id: "create-post",
        categorySlug: "posts",
        title: "How do I create a post?",
        description: "Share updates, images, or links with the community.",
        tags: ["post", "create", "share"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Select Create Post from the dashboard.",
                    "Add your text, and attach images if you'd like.",
                    "Choose who can see the post.",
                    "Select Publish.",
                ],
            },
        ],
    },
    {
        id: "delete-post",
        categorySlug: "posts",
        title: "How do I delete a post?",
        description: "Remove a post you've published.",
        tags: ["post", "delete", "remove"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Open the post you want to remove.",
                    "Select the options menu (⋯) on the post.",
                    "Select Delete Post and confirm.",
                ],
            },
        ],
    },
    {
        id: "media-upload-limits",
        categorySlug: "posts",
        title: "What are the media upload limits?",
        description: "File size and format limits for images and video.",
        tags: ["upload", "limits", "media", "size"],
        popular: false,
        content: [
            {
                type: "list",
                items: [
                    "Images: up to 8 MB each, PNG/JPG/WEBP.",
                    "Video: up to 100 MB, MP4/MOV.",
                    "Up to 10 media items per post.",
                ],
            },
        ],
    },

    // ---- Messaging ------------------------------------------------
    {
        id: "send-message",
        categorySlug: "messaging",
        title: "How do I send a message?",
        description: "Start a conversation with another member.",
        tags: ["message", "dm", "chat"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Open a member's profile.",
                    "Select Message.",
                    "Type your message and select Send.",
                ],
            },
        ],
    },
    {
        id: "block-someone",
        categorySlug: "messaging",
        title: "How do I block someone from messaging me?",
        description: "Stop receiving messages from a specific member.",
        tags: ["block", "message", "harassment"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Open the conversation with that member.",
                    "Select the options menu (⋯).",
                    "Select Block Member.",
                ],
            },
            {
                type: "note",
                text: "Blocking also prevents them from viewing your profile.",
            },
        ],
    },

    // ---- Notifications ------------------------------------------------
    {
        id: "turn-off-notifications",
        categorySlug: "notifications",
        title: "How do I turn off notifications?",
        description: "Customise which notifications you receive.",
        tags: ["notifications", "mute", "settings"],
        popular: true,
        content: [
            {
                type: "steps",
                items: [
                    "Go to Account Settings > Notifications.",
                    "Toggle off the categories you don't want to receive.",
                    "Changes save automatically.",
                ],
            },
        ],
    },
    {
        id: "email-notifications",
        categorySlug: "notifications",
        title: "How do I stop email notifications?",
        description: "Manage email-specific notification preferences.",
        tags: ["email", "notifications"],
        popular: false,
        content: [
            {
                type: "paragraph",
                text: "You can turn off email notifications separately from in-app notifications.",
            },
            {
                type: "steps",
                items: [
                    "Go to Account Settings > Notifications.",
                    "Switch to the Email tab.",
                    "Toggle off the notification types you no longer want by email.",
                ],
            },
        ],
    },

    // ---- Safety & Reporting ------------------------------------------------
    {
        id: "report-content",
        categorySlug: "safety",
        title: "How do I report a post or comment?",
        description: "Flag content that breaks community guidelines.",
        tags: ["report", "flag", "content"],
        popular: true,
        content: [
            {
                type: "steps",
                items: [
                    "Select the options menu (⋯) on the post or comment.",
                    "Select Report.",
                    "Choose the reason that best fits, and add details if needed.",
                    "Select Submit Report.",
                ],
            },
            {
                type: "note",
                text: "Reports are reviewed by our team and kept confidential.",
            },
        ],
    },
    {
        id: "report-user",
        categorySlug: "safety",
        title: "How do I report a member?",
        description: "Flag a member for harassment or abusive behaviour.",
        tags: ["report", "user", "harassment", "safety"],
        popular: false,
        content: [
            {
                type: "steps",
                items: [
                    "Open the member's profile.",
                    "Select the options menu (⋯).",
                    "Select Report Member and describe the issue.",
                ],
            },
        ],
    },

    // ---- Troubleshooting ------------------------------------------------
    {
        id: "page-not-loading",
        categorySlug: "troubleshooting",
        title: "A page isn't loading properly",
        description: "Quick fixes for pages that look broken or won't load.",
        tags: ["loading", "broken", "error", "blank page"],
        popular: true,
        content: [
            {
                type: "steps",
                items: [
                    "Refresh the page.",
                    "Clear your browser cache, or try an incognito/private window.",
                    "Make sure your browser is up to date.",
                    "If it still doesn't work, use Report a Bug below.",
                ],
            },
        ],
    },
    {
        id: "upload-failing",
        categorySlug: "troubleshooting",
        title: "My image or video won't upload",
        description: "Fix common upload errors.",
        tags: ["upload", "error", "image", "video"],
        popular: false,
        content: [
            {
                type: "list",
                items: [
                    "Check the file isn't over the size limit.",
                    "Confirm the format is supported (PNG, JPG, WEBP for images).",
                    "Try a different network connection if the upload times out.",
                ],
            },
            {
                type: "link",
                text: "See full media upload limits",
                href: "#posts",
            },
        ],
    },
    {
        id: "app-slow",
        categorySlug: "troubleshooting",
        title: "The app feels slow",
        description: "Steps to improve performance.",
        tags: ["slow", "performance", "lag"],
        popular: false,
        content: [
            {
                type: "list",
                items: [
                    "Close unused browser tabs.",
                    "Clear your browser cache.",
                    "Try a different browser to rule out an extension conflict.",
                ],
            },
        ],
    },
];