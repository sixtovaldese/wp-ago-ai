=== aGo AI Chatbot ===
Contributors: agolab
Donate link: https://paypal.me/sixtovaldes
Tags: chatbot, ai, gemini, chat, lead capture
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI chatbot powered by Google Gemini. Upload your documents, the bot answers visitors using only your content, captures leads when it cannot help.

== Description ==

aGo AI Chatbot adds a floating chat widget to your WordPress site, powered by Google Gemini. Upload your own knowledge files (PDF, TXT, CSV, Markdown, JSON) and the AI answers visitor questions using only that content. When the AI cannot find an answer, it offers a contact form and saves the lead in your dashboard.

The plugin uses the Google Gemini File Search Store: documents are indexed once and only the relevant fragments are sent to the model on each query, which keeps your API cost low.

**Main features**

* Floating chat widget on any page, configurable position (left or right) and vertical offset (3 levels) to avoid clashing with floating WhatsApp buttons.
* Knowledge base from your own files: PDF, TXT, CSV, MD, JSON.
* Configurable bot identity: name, welcome message, tone, response style, avatar.
* System prompt for fine-grained instructions.
* Automatic lead capture when the AI cannot answer, with optional WhatsApp button.
* Conversation history and task management in the admin.
* Rate limit per IP and honeypot to discourage abuse.
* Three interface languages: English, Spanish, Portuguese (Brazilian).
* Works with any theme. No code required.

**Requirements**

* A free Google Gemini API key from Google AI Studio (https://aistudio.google.com/app/apikey). You manage and pay (or use the free tier) directly with Google.

== External services ==

This plugin relies on Google Gemini, an external service operated by Google. It is used to:

* Generate chat responses from your knowledge files and visitor messages.
* Upload, store and index your knowledge files in a Gemini "File Search Store" so the model can answer with your content.
* List the Gemini models available to your API key.

What is sent: every visitor message and conversation history, your knowledge files, your Gemini API key in the request headers. No data is sent without an explicit administrator action (uploading a file, configuring the API key) or a visitor interacting with the chat widget you have enabled.

Service endpoint base: https://generativelanguage.googleapis.com/

This service is provided by Google. By using this plugin you accept Google's terms and privacy policy:

* Google Gemini API terms: https://ai.google.dev/gemini-api/terms
* Google privacy policy: https://policies.google.com/privacy

The plugin does not contact any other remote service.

== Installation ==

1. Upload the `ago-ai` folder to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to aGo Tools, then AI Chatbot.
4. Enter your Gemini API key (free at https://aistudio.google.com/app/apikey).
5. Upload one or more knowledge files.
6. Configure the bot identity and the widget appearance.
7. Tick "Enable Chatbot" and save. The widget appears on the frontend.

== Frequently Asked Questions ==

= Do I need a paid API key? =

No. Google Gemini offers a free tier in Google AI Studio that is enough for most small sites. Costs depend on the model and volume; verify pricing at ai.google.dev/pricing.

= Where is my data stored? =

Your settings, conversations and captured leads live in your WordPress database. Your knowledge files are uploaded to Google Gemini under your own API key. The plugin does not send data anywhere else.

= Can the bot answer about general topics? =

By default the bot is instructed to answer only using your uploaded files. If a visitor asks something off-topic, the bot redirects them politely.

= What file types can I upload? =

PDF, TXT, CSV, Markdown (.md) and JSON.

= Does it work with Elementor, Divi, Bricks or other builders? =

Yes. The widget is rendered via wp_footer, independent of the theme or page builder.

= Can I disable the floating widget on specific pages? =

The widget appears site-wide when enabled. You can toggle it from the settings page. Per-page control is planned for a future release.

= What languages does the chatbot speak? =

The AI replies in the same language the visitor writes in. The admin interface ships in English, Spanish and Portuguese (Brazilian).

== Screenshots ==

1. The floating chat widget on a WordPress site.
2. Admin settings: general, personality, knowledge base.
3. Conversation history and captured leads.
4. Knowledge base upload (PDF, TXT, CSV, MD, JSON).

== Changelog ==

= 1.0.0 =
* Initial public release.
* Google Gemini integration with File Search Store.
* Knowledge base upload (PDF, TXT, CSV, MD, JSON).
* Configurable chat widget (position, vertical offset, color, avatar).
* Bot personality (name, tone, response style, system prompt).
* Lead capture with optional WhatsApp button.
* Conversation history and task management.
* Translations: English, Spanish, Portuguese (Brazilian).

== Upgrade Notice ==

= 1.0.0 =
Initial release.
