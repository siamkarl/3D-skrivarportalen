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
- **Node.js** - För asynkrona funktioner och uppdateringar i realtid.
- **PHPMailer** - Används för att skicka e-post (kontoåterställning och verifiering).
- **Composer** - Hanterar PHP-bibliotek och beroenden.

## Installation

1. Klona detta repository:
   ```sh
   git clone https://github.com/ditt-användarnamn/3d-skrivare-portal.git
   cd 3d-skrivare-portal
   ```

2. Installera PHP-beroenden:
   ```sh
   composer install
   ```

3. Installera Node.js-beroenden:
   ```sh
   npm install
   ```

4. Konfigurera `.env`-filen med dina inställningar (databas, e-postserver osv.).

5. Starta servern:
   ```sh
   php -S localhost:8000 -t public/
   ```

6. Starta eventuella Node.js-processer:
   ```sh
   node server.js
   ```

## Bidrag

Pull requests är välkomna! För större förändringar, vänligen öppna en issue först för att diskutera vad du vill ändra.

## Licens

Detta projekt är licensierat under MIT-licensen. Se [LICENSE](LICENSE) för mer information.
