# Bones pràctiques de programació – SPRINT1-KICKOFF

Aquest document recull les bones pràctiques aplicades al projecte de gestió de mobilitat intel·ligent, tant a nivell de codi com d’estructura, seguretat i mantenibilitat.

---

## 1. Estructura clara i modular

- **Separació per funcionalitat:** El projecte està organitzat en carpetes per domini (configuració, frontend, backend, scripts Python).
- **MVC al backend:** El codi PHP segueix el patró Model-Vista-Controlador amb controladors, models i components separats.
- **DocumentRoot separat:** Els arxius públics (`public_html/`) estan aïllats de la lògica backend.

## 2. Bones pràctiques de codi

- **Noms descriptius:** Variables, funcions i arxius amb noms clars i coherents.
- **Comentaris explicatius:** El codi inclou comentaris als punts clau i als fitxers principals.
- **Modularitat:** Scripts JavaScript separats per funcionalitat (`auth.js`, `booking.js`, `vehicles.js`, etc.).
- **Reutilització:** Components PHP i JS reutilitzables (header, footer, utilitats).

## 3. Seguretat

- **Gestió de sessions:** Autenticació i control d’accés amb sessions PHP.
- **Validació de dades:** Validació tant al frontend (JS) com al backend (PHP).
- **Control de versions:** `.gitignore` actualitzat per evitar exposar credencials i arxius sensibles.
- **Pagaments segurs:** Pre-autorizació de targeta i gestió de pagaments amb lògica dedicada.

## 4. Accessibilitat

- **Estils dedicats:** Fulls d’estil com `accessibility.css` per millorar la visibilitat i l’accessibilitat.
- **Widget UserWay:** Integració d’eina d’accessibilitat.
- **Etiquetes semàntiques i ARIA:** HTML amb etiquetes i atributs per suport a lectors de pantalla.
- **Opcions d’accessibilitat:** Ajust de contrast, mida de text, navegació per teclat i reducció de moviment.

## 5. Internacionalització

- **Sistema d’idiomes:** Fitxers JSON per cada idioma (`ca.json`, `en.json`, `es.json`) i gestió dinàmica en PHP/JS.
- **Tutorials per idioma:** Arxius de tutorial separats per llengua.

## 6. Escalabilitat i mantenibilitat

- **Bases de dades poliglot:** MariaDB per dades estructurades i MongoDB per logs i sensors.
- **Docker:** Orquestració de serveis per facilitar el desplegament i la portabilitat.
- **Scripts d’inicialització:** Automatització de la creació de taules i col·leccions.

## 7. Desenvolupament col·laboratiu

- **Forks i pull requests:** Recomanats per afegir funcionalitats o corregir errors.
- **Documentació:** README detallat i fitxer de bones pràctiques.
- **Comentaris als fitxers clau:** Faciliten la comprensió del flux i la lògica.

## 8. Control de versions i privacitat

- **.gitignore complet:** Exclou arxius temporals, credencials, dependències i carpetes de desenvolupament.
- **No es versionen dades sensibles:** Les credencials i configuracions privades es mantenen fora del repositori.

---
