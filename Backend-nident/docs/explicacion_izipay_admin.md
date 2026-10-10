Es decir: primero lee la base de datos (lo que configuras en la interfaz), y solo si está vacío usa el .env como fallback.

En tu .env de backend tienes estas mismas claves, como respaldo:

IZIPAY_ENV=sandbox
IZIPAY_USERNAME=13478150
IZIPAY_PASSWORD=...
IZIPAY_PUBLIC_KEY=13478150:testpublickey_d...
IZIPAY_SHA256_KEY=...
El .env del frontend (prestomart-Front/.env) no lleva credenciales de Izipay; solo settings de Vite/tema.

2. ¿Los inputs de la interfaz funcionan o solo sirve el .env?
Sí, los inputs de la interfaz funcionan:

Al guardar en la pantalla de pasarela, el frontend envía las claves a pago_pasarela_config.
El backend (PaymentGatewayManager::getConfig) lee primero esas claves de la tabla.
Solo si en la tabla está vacío recurre al .env.
En tu caso actual:

Lo que pongas en la interfaz para Username, Public Key, SHA-256 Key y Password es lo que usará Izipay.
El .env queda como copia de seguridad o para inicializar valores por defecto.
3. Labels poco claros (“Password” pero en código no veo “password”)
Aclarado y corregido en la UI:

El nombre interno de la variable es IZIPAY_PASSWORD (coincide con tu .env).
En la base de datos, la etiqueta ya era “Password”, pero no se veía claro el mapeo.
Acabo de mejorar la pantalla PasarelaIndexPage.jsx para que, en Culqi e Izipay, cada campo muestre:

Etiqueta amigable + nombre de variable. Por ejemplo:

Username (Merchant ID) · IZIPAY_USERNAME
Public Key · IZIPAY_PUBLIC_KEY
Password · IZIPAY_PASSWORD
SHA-256 Key (HMAC Signature) · IZIPAY_SHA256_KEY
Entorno (sandbox | production) · IZIPAY_ENV
Y en el placeholder (cuando no es secreto) ahora verás algo como:

IZIPAY_USERNAME (se usa en el backend/.env)
Así, desde la propia interfaz sabes exactamente qué variable del backend estás editando y cómo se llama en .env.

4. Resumen corto
Sí: los campos de la interfaz (admin) son los que manda la integración; el .env solo es fallback.
He alineado las etiquetas de la UI para que se vean nombre descriptivo + nombre técnico de la variable (IZIPAY_* / CULQI_*).
Con esto ya tienes claro qué escribir en cada input y cómo se relaciona con las variables del código.