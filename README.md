# aGo AI Chatbot

> AI chatbot for WordPress powered by Google Gemini. Upload your documents, the bot answers visitors using only your content, captures leads when it cannot help.

[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg)](https://wordpress.org)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777bb4.svg)](https://www.php.net)

Versión en español más abajo · [Spanish version below](#aGo-ai-chatbot-español)

---

## What it does

This plugin adds a floating chat widget to any WordPress site. Visitors can ask questions and the bot answers using only the files you have uploaded as knowledge base (PDF, TXT, CSV, Markdown, JSON). When the AI cannot find an answer, it offers the visitor a contact form and the lead lands in your admin.

The model used is Google Gemini. The API key is yours, the data stays in your database, the bot does not invent information.

## Main features

- Floating widget on any page. Position (left or right) and vertical offset (3 levels) so it does not clash with a WhatsApp floating button.
- Knowledge base from your own files. Supported: PDF, TXT, CSV, MD, JSON.
- Configurable bot identity: name, welcome message, tone, response style, avatar.
- Custom system prompt for fine instructions.
- 50 chats per day per visitor (built-in fair-use cap).
- Three interface languages: English, Spanish, Portuguese.
- Works with any theme. No code required.
- Clean uninstall: when you remove the plugin, every option and table is cleaned.

## Requirements

- WordPress 6.0 or newer
- PHP 8.1 or newer
- A free Google Gemini API key (get one at [aistudio.google.com](https://aistudio.google.com/app/apikey))

## Installation

1. Download the latest [release ZIP](../../releases) or clone this repository into `wp-content/plugins/ago-ai`.
2. Activate the plugin from the WordPress admin.
3. Open `aGo Tools → AI Chatbot`.
4. Paste your Gemini API key and save.
5. Upload one knowledge file (PDF works great for FAQs, catalogues, manuals).
6. Customize the bot personality and widget appearance.
7. Tick **Enable Chatbot** and save.

The floating widget appears on the frontend immediately.

## Privacy

- Visitor messages and uploaded files go to Google Gemini under **your** API key.
- Captured leads (name, email, message) live only in your WordPress database.
- The plugin does not contact any other remote service.
- No analytics, no telemetry, no tracking.

Google Gemini terms: [ai.google.dev/gemini-api/terms](https://ai.google.dev/gemini-api/terms). Google privacy policy: [policies.google.com/privacy](https://policies.google.com/privacy).

## Pro version

A paid Pro version is available at [store.ago.cl](https://store.ago.cl) with extra features for teams that need them: unlimited daily chats, unlimited knowledge files, full conversation history, lead capture and task panel, WhatsApp button. The Pro is a separate plugin and is **not required** for this Lite version to be fully functional. Buy it only if you outgrow the free version.

## Contributing

Issues and PRs are welcome. Open an issue first if you want to discuss a feature or a bigger change. For bugs, please include WordPress version, PHP version, theme, and steps to reproduce.

If you ship a translation for another locale, drop a pull request with the `.l10n.php` file inside `languages/`.

## Support the project

If this plugin saves you time, consider buying me a coffee:

- [PayPal — single donation](https://paypal.me/sixtovaldes)
- [Buy Me a Coffee](https://www.buymeacoffee.com/sixtovaldese)

I build the [aGo plugin suite](https://ago.cl) in my spare time. Donations go toward keeping these plugins maintained and open source.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

## Credits

Made by [Sixto Valdés](https://github.com/sixtovaldese), founder of [aGo lab](https://ago.cl) in Chillán, Chile.

---

# aGo AI Chatbot (español)

> Chatbot de IA para WordPress con Google Gemini. Sube tus documentos, el bot responde a los visitantes usando solo tu contenido, captura leads cuando no puede ayudar.

## Qué hace

Este plugin agrega un widget de chat flotante a cualquier sitio WordPress. Los visitantes preguntan y el bot responde usando solo los archivos que hayas subido como base de conocimiento (PDF, TXT, CSV, Markdown, JSON). Cuando la IA no encuentra la respuesta, le ofrece al visitante un formulario de contacto y el lead llega a tu admin.

El modelo es Google Gemini. La API key es tuya, los datos quedan en tu base de datos, el bot no inventa información.

## Funcionalidades principales

- Widget flotante en cualquier página. Posición (izquierda o derecha) y altura vertical (3 niveles) para no chocar con un botón flotante de WhatsApp.
- Base de conocimiento desde tus propios archivos. Soportados: PDF, TXT, CSV, MD, JSON.
- Identidad del bot configurable: nombre, mensaje de bienvenida, tono, estilo de respuesta, avatar.
- System prompt personalizable para instrucciones finas.
- 50 chats al día por visitante (límite de uso justo).
- Tres idiomas en la interfaz: inglés, español, portugués.
- Compatible con cualquier theme. Sin tocar código.
- Desinstalación limpia: al borrar el plugin se eliminan todas las opciones y tablas.

## Requisitos

- WordPress 6.0 o superior
- PHP 8.1 o superior
- Una API key gratuita de Google Gemini (la obtienes en [aistudio.google.com](https://aistudio.google.com/app/apikey))

## Instalación

1. Descarga el [ZIP de la última release](../../releases) o clona este repositorio en `wp-content/plugins/ago-ai`.
2. Activa el plugin desde el admin de WordPress.
3. Entra a `aGo Herramientas → AI Chatbot`.
4. Pega tu API key de Gemini y guarda.
5. Sube un archivo de conocimiento (un PDF con FAQ, catálogo o manual funciona muy bien).
6. Personaliza la personalidad del bot y la apariencia del widget.
7. Marca **Activar Chatbot** y guarda.

El widget flotante aparece en el frontend al instante.

## Privacidad

- Los mensajes de los visitantes y los archivos subidos van a Google Gemini con **tu** API key.
- Los leads capturados (nombre, email, mensaje) viven solo en la base de datos de tu sitio.
- El plugin no contacta ningún otro servicio remoto.
- Sin analítica, sin telemetría, sin tracking.

Términos de Google Gemini: [ai.google.dev/gemini-api/terms](https://ai.google.dev/gemini-api/terms). Política de privacidad de Google: [policies.google.com/privacy](https://policies.google.com/privacy).

## Versión Pro

Hay una versión Pro de pago en [store.ago.cl](https://store.ago.cl) con funciones extra para quienes las necesitan: chats diarios ilimitados, archivos de conocimiento ilimitados, historial de conversaciones, captura de leads con panel de tareas, botón de WhatsApp. El Pro es un plugin separado y **no es necesario** para que esta versión Lite funcione completa. Solo compra el Pro si te quedas corto con la versión gratis.

## Contribuir

Los issues y pull requests son bienvenidos. Si quieres discutir una nueva función o un cambio grande, abre un issue primero. Para reportar bugs incluye la versión de WordPress, de PHP, el theme que usas y los pasos para reproducir.

Si traduces el plugin a otro idioma, abre un PR con el archivo `.l10n.php` dentro de `languages/`.

## Apoyar el proyecto

Si este plugin te ahorra tiempo, considera invitarme un café:

- [PayPal — donación única](https://paypal.me/sixtovaldes)
- [Buy Me a Coffee](https://www.buymeacoffee.com/sixtovaldese)

Desarrollo la [suite de plugins aGo](https://ago.cl) en mi tiempo libre. Las donaciones ayudan a mantener estos plugins activos y open source.

## Licencia

GPL-2.0-or-later. Ver [LICENSE](LICENSE).

## Créditos

Hecho por [Sixto Valdés](https://github.com/sixtovaldese), fundador de [aGo lab](https://ago.cl) en Chillán, Chile.
