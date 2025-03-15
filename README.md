# 3D Skrivare Portal

Det här projektet är en webbaserad portal för hantering av 3D-skrivare med funktioner som inloggning, bokning av skrivare, rapportering av problem och återställning av lösenord via e-post. Systemet är byggt med PHP (version 8.4) och Node.js för att förbättra funktionalitet och användarupplevelse.

# 3D Skrivare Portal

![3D Printer Logo](https://github.com/siamkarl/nti3dskrivare/blob/main/logo.png)

Det här projektet är en webbaserad portal för hantering av 3D-skrivare med funktioner som inloggning, bokning av skrivare, rapportering av problem och återställning av lösenord via e-post. Systemet är byggt med PHP (version 8.4) och Node.js för att förbättra funktionalitet och användarupplevelse.

## Funktioner

- **Login-system för elever (elev.ga.ntig.se) och personal (ga.ntig.se)**  
  Inloggning med specifika e-postdomäner för att säkerställa att endast behöriga användare får åtkomst.
  ![Login System](https://github.com/siamkarl/nti3dskrivare/blob/main/scrnli_FagHJ0bnDhb16U.png)

- **Bokningssystem för 3D-skrivare**  
  Användare kan boka 3D-skrivare för användning under specificerade tider och datum.
  ![Booking System](https://your-repository-link.com/path/to/booking-screenshot.png)
  
- **Rapportering av problem med 3D-skrivare till administrationen**  
  Användare kan rapportera problem med 3D-skrivarna till administrationen för att få hjälp och support.
  ![Reporting Problems](https://your-repository-link.com/path/to/reporting-screenshot.png)
  
- **Avbokning av bokningar vid behov**  
  Möjlighet att avboka bokningar om användaren inte längre behöver skriva ut.
  ![Cancel Booking](https://your-repository-link.com/path/to/cancel-booking-screenshot.png)

- **Återställning av lösenord via SMTP-mailserver**  
  Användare kan återställa sina lösenord genom att få en länk via e-post (SMTP-server).
  ![Password Reset](https://your-repository-link.com/path/to/password-reset-screenshot.png)

## Teknologier

- **PHP (version 8.4)**  
  Backend-kod för autentisering, databashantering och serverlogik.
  
- **Node.js**  
  För att förbättra vissa funktioner och användarupplevelse (t.ex. asynkrona funktioner och uppdateringar i realtid).
  
- **PHPMailer**  
  Används för att skicka e-post, inklusive e-post för kontoverifiering och lösenordsåterställning.

- **Composer**  
  Används för att hantera PHP-bibliotek och beroenden som PHPMailer.

## Installation

Följ dessa steg för att installera och köra projektet lokalt.

### Förberedelser

1. Se till att du har PHP version 8.4 installerat. Du kan kontrollera din PHP-version med följande kommando:
   ```bash
   php -v
