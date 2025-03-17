# 3D Skrivare Portal

Det här projektet är en webbaserad portal för hantering av 3D-skrivare med funktioner som inloggning, bokning av skrivare, rapportering av problem och återställning av lösenord via e-post. Systemet är byggt med PHP (version 8.4) och Node.js för att förbättra funktionalitet och användarupplevelse.

## Funktioner

- **Login-system för elever och personal**
  - Elever loggar in via `elev.ga.ntig.se`.
  - Personal loggar in via `ga.ntig.se`.
  - Endast behöriga användare kan logga in via specifika e-postdomäner.

- **Bokningssystem för 3D-skrivare**
  - Användare kan boka 3D-skrivare för användning vid specificerade tider och datum.
  - Möjlighet att avboka bokningar vid behov.
  
- **Rapportering av problem**
  - Användare kan rapportera problem med 3D-skrivarna till administrationen.
  - En supportsida visar status för problem och lösningar.

- **Återställning av lösenord**
  - Användare kan återställa sina lösenord via e-post (SMTP-server).

- **Rollsystem**
  - Administratörer har särskilda behörigheter för hantering av systemet.
  - Vanliga användare har begränsad åtkomst enligt sina roller.

- **Underhållsläge**
  - Administratörer kan planera och ta bort underhållsperioder via adminpanelen.
  - Systemet visar en underhållsstatus om det är nere.
  - Automatiska e-postmeddelanden skickas om bokningar påverkas av underhåll.
  - Administratörer kan lägga till underhållsarbete via dashboarden.

- **Regelsida & Guide**
  - En sida med regler för användning av 3D-skrivarna.
  - Guide för hur man byter filament för varje skrivare.
  - Instruktioner för hur man lämnar skrivaren i rätt skick efter användning.

- **Väderintegration**
  - Dashboarden visar aktuellt väder i Kristianstad via ett API.

- **Projekt- och processhantering**
  - Administratörer kan lägga in projekt och definiera processflödet för varje projekt.
  - Användare kan följa projektets status och framsteg.

## Teknologier

- **PHP (version 8.4)** - Backend-kod för autentisering, databashantering och serverlogik.
- **MySQL eller MariaDB** - Databas för lagring av användardata, bokningar och systeminställningar.
- **PHPMailer** - Används för att skicka e-post (kontoåterställning och verifiering).
- **Composer** - Hanterar PHP-bibliotek och beroenden.

## Att fixa

- **Återställning** av lösenord behöver förbättras.
- **Verifiering** av användare saknas eller behöver åtgärdas.
- **Back-knappar** saknas på vissa sidor och behöver läggas till.

## Installation av Database

- **Importera** admin_skola.sql
- **Ändra database inställningar i filerna** (finns flera filer)
- **Inloggning admin** användare: admin, password: 1234
- **Inloggning user** användare: user, password: 1234

## Teknologier

- **PHP (version 8.4)** - Backend-kod för autentisering, databashantering och serverlogik.
- **MySQL eller MariaDB** - Databas för lagring av användardata, bokningar och systeminställningar.
- **PHPMailer** - Används för att skicka e-post (kontoåterställning och verifiering).
- **Composer** - Hanterar PHP-bibliotek och beroenden.

## Smtp server redo att användas för utbildningssyfte
- **mail server:** mail.ntiare.se
- **port:** 587
- **username:** sender@ntiare.se
- **password:** P7DRQntPHv9c6vP2V5Ky


## Bidrag

Pull requests är välkomna! För större förändringar, vänligen öppna en issue först för att diskutera vad du vill ändra.

## Licens

Detta projekt är licensierat under MIT-licensen. Se [LICENSE](LICENSE) för mer information.
