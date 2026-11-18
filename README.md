# Woorden Verbinden — vertaler & tolk

Een eenvoudige PHP MVC-website voor freelance vertaal- en tolkdiensten in het Nederlands, Engels en Arabisch.

## Structuur

- `index.php` — entry point en router (`?page=diensten`, `?page=over-mij`, `?page=contact`)
- `app/Controllers/` — afzonderlijke controller per pagina
- `app/Views/layout.php` — gedeelde HTML-layout en navbar
- `app/Views/` — afzonderlijke views voor startpagina, diensten, over mij en contact
- `app/Models/ContactRequest.php` — verwerkt contactaanvragen en gebruikt een mailinterface
- `app/Contracts/MailerInterface.php` — contract voor verwisselbare mailstrategieën
- `app/Validation/ContactRequestValidator.php` — servervalidatie
- `app/Services/SmtpMailer.php` — SMTP-strategie die aanvragen rechtstreeks via Gmail verstuurt
- `app/Services/AppLogger.php` — logt PHP-, SMTP- en applicatiefouten in afgeschermde opslag
- `app/config.local.php` — lokale mailinstellingen en geheimen (genegeerd door Git)
- `public/css/style.css` — responsive ontwerp
- `public/js/app.js` — taalkeuze, RTL-weergave en mobiele navigatie

## Lokaal starten

Vereist PHP 8+. Vanuit deze map:

```powershell
& 'C:\xampp\php\php.exe' -S localhost:8000 router.php
```

Open daarna `http://localhost:8000`.

## E-mail instellen

Tijdens lokaal ontwikkelen gebruikt het formulier standaard de testmail-strategie. Aanvragen worden dan lokaal vastgelegd; er wordt geen e-mail verstuurd. Zet `driver` in `app/config.local.php` op `smtp` om echte e-mail via Gmail te verzenden. XAMPP `sendmail.ini` en Microsoft OAuth zijn hiervoor niet nodig.

1. Zet tweestapsverificatie aan voor het Gmail-account dat als verzender dient en maak een Google-app-wachtwoord aan bij [Google App Passwords](https://myaccount.google.com/apppasswords).
2. Kopieer `app/config.example.php` naar `app/config.local.php`.
3. Vul in `app/config.local.php` je Gmail-adres in bij `username`, het app-wachtwoord bij `password` en het ontvangstadres bij `to_email`. Stel `driver` in op `smtp`. De bezoeker die het formulier invult wordt automatisch als `Reply-To` ingesteld. Laat SMTP-host `smtp.gmail.com`, poort `587` en versleuteling `tls` staan.
4. Herstart de PHP-server. Antwoorden gaan naar het e-mailadres dat de bezoeker in het formulier invult.

`app/config.local.php` staat in `.gitignore` en wordt niet naar Git meegenomen. Deel het app-wachtwoord niet en zet het niet in andere bronbestanden.
MySQL is voor deze versie niet vereist. Voeg een database pas toe als aanvragen bewaard, doorzocht of beheerd moeten worden.

## Formulier testen zonder e-mail

Laat `driver` op `test` staan (dit is ook de standaard als de instelling ontbreekt). Verstuur een aanvraag via het contactformulier. De groene terugkoppeling meldt dat de test is geslaagd en dat er geen e-mail is verstuurd. De aanvraag wordt lokaal opgeslagen in `storage/test-mail.jsonl`, buiten de publieke map en uitgesloten van Git. Dit bestand kan persoonsgegevens bevatten; verwijder het na de test als je de inzendingen niet wilt bewaren.

## Applicatiefouten bekijken

PHP-waarschuwingen, fatale fouten, niet-opgevangen uitzonderingen en SMTP-fouten worden in leesbare tekst gelogd in `storage/log.txt`. Open het bestand vanuit de projectmap met VS Code of Kladblok. De opslagmap is via Apache en via de meegeleverde PHP-router afgeschermd. De logger schrijft geen stacktrace-argumenten of formulierinhoud weg en filtert contextvelden die op wachtwoorden of tokens wijzen.

## Aanpassen voor publicatie

Vervang de voorbeeldnaam/branding en illustratie door echte gegevens en een portretfoto. Vul ook de echte contactgegevens en eventuele kwalificaties in voordat de site live gaat.

## Bestanden uploaden via het contactformulier

Het contactformulier accepteert optioneel PDF-, DOC-, DOCX-, TXT-, JPG- en PNG-bestanden tot maximaal 2 MB. PHP controleert zowel de bestandsgrootte als de extensie en het MIME-type voordat het bestand als e-mailbijlage wordt meegestuurd.

Controleer in `C:\\xampp\\php\\php.ini` dat `upload_max_filesize` op minimaal `2M` en `post_max_size` op minimaal `4M` staat. Herstart daarna de PHP-server. De applicatie weigert uploads boven 2 MB altijd zelf.

## Servervalidatie

Servervalidatie staat apart in `app/Validation/ContactRequestValidator.php`. De validator controleert verplichte velden, e-mailadres, toegestane dienst, lengte van naam en bericht, en uploadtype en -grootte. De HTML-velden hebben ook `required` voor directe browservalidatie; servervalidatie blijft altijd van kracht.
