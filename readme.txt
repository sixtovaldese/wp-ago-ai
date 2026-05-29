=== aGo AI Chatbot, AI Chat Widget with Knowledge Base ===
Contributors: sixtovaldese
Donate link: https://paypal.me/sixtovaldes
Tags: ai chatbot, ai chat, chat widget, customer support, ai assistant
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free AI chat widget for WordPress powered by Google Gemini. Upload your knowledge files and the AI answers visitor questions using your own content.

== Description ==

**aGo AI Chatbot** is a free AI chat widget for WordPress. Add a floating chatbot to any site in minutes. Upload your own knowledge files (PDF, TXT, CSV, Markdown, JSON) and the AI answers visitor questions using that content, in their language, around the clock.

**This plugin is free and fully functional.** No chat caps, no file limits, no signup, no upsell modals. You use your own Google Gemini API key (Google offers a generous free tier). The plugin never contacts any aGo server.

= Why aGo AI Chatbot? =

* **Your content, your answers.** The bot grounds its responses on the documents *you* upload. No hallucinations from random training data.
* **Free, not freemium.** The plugin is 100% functional out of the box. There is no usage cap to pressure you into a paid version.
* **Privacy-first.** Visitor messages go to Google under *your* API key. The plugin does not store conversations, does not phone home, does not track anyone.
* **Lightweight.** ~30 KB widget JS. No CDN dependencies. Works on any theme.
* **Multilingual.** The AI replies in the language the visitor writes. Admin UI in English, Spanish, Portuguese (Brazilian).

= Core features =

* **AI chat widget** in any page (configurable position: left/right, vertical offset to avoid clashing with floating buttons).
* **Knowledge base from your own files**, PDF, TXT, CSV, Markdown, JSON. **Unlimited** uploads.
* **Configurable bot identity**, name, welcome message, tone (friendly / professional / casual / formal), response style, avatar.
* **System prompt** for fine-grained instructions (industry, audience, rules).
* **Google Gemini File Search Store** under the hood, documents are indexed once, only the relevant fragments are sent to the model, keeping your API cost low.
* **Anti-abuse rate limit** per IP (default 60 messages/minute, configurable) to protect your Gemini quota from automated flooding.
* **Works with every page builder**: Elementor, Divi, Bricks, Gutenberg, classic, the widget is rendered via `wp_footer` regardless of the theme.

= Use cases =

* Customer support assistant trained on your help articles.
* Product Q&A bot trained on your product datasheets.
* Documentation assistant that searches your manuals.
* Knowledge worker assistant that knows your company's internal handbook.
* Real estate assistant that knows your listings.
* Restaurant assistant that knows your menu and hours.

= Requirements =

* A free Google Gemini API key from Google AI Studio (https://aistudio.google.com/app/apikey). You manage and pay (or use the free tier) directly with Google.

= Powered by =

This plugin uses the Google Gemini API ("Gemini" is a trademark of Google LLC). aGo Lab is not affiliated with Google.

= Pro companion (optional, not on WordPress.org) =

A separate paid companion plugin (distributed only from store.ago.cl) adds features that this free plugin does NOT include: conversation history, lead capture forms, multi-provider failover (OpenAI/Anthropic/Groq/Cohere), analytics dashboard, voice input/output, handoff to human (Email/Slack/Telegram), WooCommerce product knowledge, white-label. The free plugin works fully on its own; the Pro is purely additive.

== External services ==

This plugin relies on **Google Gemini**, an external service operated by Google. It is used to:

* Generate chat responses from your knowledge files and visitor messages.
* Upload, store and index your knowledge files in a Gemini "File Search Store" so the model can answer with your content.
* List the Gemini models available to your API key.

**What is sent**: every visitor message and conversation history (while the chat is open), your knowledge files, your Gemini API key in the request headers. No data is sent without an explicit administrator action (uploading a file, configuring the API key) or a visitor interacting with the chat widget you have enabled.

**Service endpoint base**: https://generativelanguage.googleapis.com/

This service is provided by Google. By using this plugin you accept Google's terms and privacy policy:

* Google Gemini API terms: https://ai.google.dev/gemini-api/terms
* Google privacy policy: https://policies.google.com/privacy

The plugin does not contact any other remote service. No data is sent to aGo Lab.

== Installation ==

1. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**, choose the ZIP, install and activate. Alternatively, upload the `ago-ai` folder to `/wp-content/plugins/` via FTP.
2. Get your free Gemini API key at https://aistudio.google.com/app/apikey.
3. Go to **aGo Tools → AI Chatbot** in your WordPress admin.
4. Paste your Gemini API key and save.
5. Upload one or more knowledge files (PDF, TXT, CSV, MD, JSON).
6. Configure the bot identity and widget appearance.
7. Tick **Enable Chatbot** and save. The widget appears on the frontend.

== Frequently Asked Questions ==

= Is this plugin really free? =

Yes. The plugin is 100% free with no usage caps, no premium gating, no signup. You use your own Google Gemini API key.

= Do I need a paid API key? =

No. Google Gemini offers a generous free tier in Google AI Studio that is enough for most small and medium sites. Costs depend on the model and volume; check pricing at ai.google.dev/pricing.

= Does the chatbot work on mobile? =

Yes. The widget is responsive and adapts to small screens.

= Where is my data stored? =

Your settings live in your WordPress database. Your knowledge files are uploaded to Google Gemini under your own API key. The plugin does not store visitor conversations and does not send data anywhere else.

= Can the bot answer about general topics? =

By default the bot is instructed to answer only using your uploaded files. If a visitor asks something off-topic, the bot redirects them politely. You can soften or harden this behavior with the System Prompt.

= What file types can I upload as knowledge? =

PDF, TXT, CSV, Markdown (.md) and JSON. Unlimited number of files.

= Does it work with Elementor, Divi, Bricks, Beaver Builder or Gutenberg? =

Yes. The widget is rendered via `wp_footer`, independent of the theme or page builder.

= Can I customize the look of the widget? =

Yes, position (left/right), vertical offset (3 levels), primary color, bot avatar, welcome message and bot name.

= Can I disable the floating widget on specific pages? =

The widget appears site-wide when enabled. You can toggle it from the settings page. Per-page control is planned for a future release.

= What languages does the chatbot speak? =

The AI replies in the same language the visitor writes in. The admin interface ships in English, Spanish and Portuguese (Brazilian).

= Does this plugin send any data to aGo Lab? =

No. All AI calls go directly from your server to Google Gemini under your own API key. aGo Lab does not receive any data.

= How do I uninstall it cleanly? =

Deactivate and delete from the Plugins screen. The plugin removes its options on uninstall.

== Screenshots ==

1. The floating AI chat widget on a WordPress site.
2. Admin settings: general configuration, bot personality, knowledge base.
3. Knowledge file upload (PDF, TXT, CSV, MD, JSON).
4. Widget appearance: position, color, avatar.

== Changelog ==

= 1.2.0 =
* Prefixed all internal identifiers with `agoaichat` / `AGOAICHAT_` and namespace `AgoLab\AIChatbot` to avoid conflicts with other plugins.
* REST namespace renamed to `ago-ai-chatbot/v1`.
* The public chat endpoint now verifies the widget is enabled before proxying any request.
* Removed "Free" from the plugin display name.
* Fixed the rate limit field in settings (now correctly reads and saves messages per minute).

= 1.1.0 =
* Removed all built-in usage caps. Plugin is now fully functional without restrictions.
* Removed daily chat limit and file count limit. Knowledge files are now unlimited.
* Replaced previous rate limit with a configurable anti-abuse limit per IP per minute.
* Renamed text domain to `ago-ai-chatbot` to match the plugin slug.
* Improved output escaping (`wp_kses_post` on the admin footer).
* Removed self-installing translation files (relying on standard WordPress mechanism).
* Plugin URI now points to public documentation.

= 1.0.0 =
* Initial public release.
* Google Gemini integration with File Search Store.
* Knowledge base upload (PDF, TXT, CSV, MD, JSON).
* Configurable chat widget (position, vertical offset, color, avatar).
* Bot personality (name, tone, response style, system prompt).
* Translations: English, Spanish, Portuguese (Brazilian).

== Upgrade Notice ==

= 1.2.0 =
Unique internal prefixes, hardened public chat endpoint, and naming fixes required for WordPress.org compliance.

= 1.1.0 =
Fully functional, no built-in limits. Renamed text domain to match the plugin slug. Required for WordPress.org compliance.
