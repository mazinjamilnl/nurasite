# Woorden Verbinden — vertaler & tolk

Een eenvoudige PHP MVC-website voor freelance vertaal- en tolkdiensten in het Nederlands, Engels en Arabisch.

## Structuur

- `index.php` — entry point en router (`?page=diensten`, `?page=over-mij`, `?page=contact`)
- `app/Controllers/` — afzonderlijke controller per pagina
- `app/Views/layout.php` — gedeelde HTML-layout en navbar
- `app/Views/` — afzonderlijke views voor startpagina, diensten, over mij en contact
- `app/Models/ContactRequest.php` — valideert en verstuurt contactaanvragen
- `public/css/style.css` — responsive ontwerp
- `public/js/app.js` — taalkeuze, RTL-weergave en mobiele navigatie

## Lokaal starten

Vereist PHP 8+. Vanuit deze map:

```sh
php -S localhost:8000
```

Open daarna `http://localhost:8000`.

## E-mail instellen

Het formulier gebruikt PHP `mail()`. Stel `CONTACT_EMAIL` in op het ontvangstadres en configureer op de hostingserver een SMTP/sendmail-transport. Zonder deze instelling toont de site een duidelijke melding en wordt er geen aanvraag als verzonden gemeld.

MySQL is voor deze versie niet vereist. Voeg een database pas toe als aanvragen bewaard, doorzocht of beheerd moeten worden.

## Aanpassen voor publicatie

Vervang de voorbeeldnaam/branding en illustratie door echte gegevens en een portretfoto. Vul ook de echte contactgegevens en eventuele kwalificaties in voordat de site live gaat.
