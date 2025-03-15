# 3D Skrivare Portal

Det här projektet är en webbaserad portal för hantering av 3D-skrivare med funktioner som inloggning, bokning av skrivare, rapportering av problem och återställning av lösenord via e-post. Systemet är byggt med PHP (version 8.4) och Node.js för att förbättra funktionalitet och användarupplevelse.

## Funktioner

- **Login-system för elever (elev.ga.ntig.se) och personal (ga.ntig.se)**  
  Inloggning med specifika e-postdomäner för att säkerställa att endast behöriga användare får åtkomst.
  ![Login System](https://github.com/siamkarl/nti3dskrivare/blob/main/scrnli_FagHJ0bnDhb16U.png)

- **Bokningssystem för 3D-skrivare**  
  Användare kan boka 3D-skrivare för användning under specificerade tider och datum.
  
- **Rapportering av problem med 3D-skrivare till administrationen**  
  Användare kan rapportera problem med 3D-skrivarna till administrationen för att få hjälp och support.
  
- **Avbokning av bokningar vid behov**  
  Möjlighet att avboka bokningar om användaren inte längre behöver skriva ut.

- **Återställning av lösenord via SMTP-mailserver**  
  Användare kan återställa sina lösenord genom att få en länk via e-post (SMTP-server).

## Teknologier

- **PHP (version 8.4)**  
  Backend-kod för autentisering, databashantering och serverlogik.
  
- **Node.js**  
  För att förbättra vissa funktioner och användarupplevelse (t.ex. asynkrona funktioner och uppdateringar i realtid).
  
- **PHPMailer**  
  Används för att skicka e-post, inklusive e-post för kontoverifiering och lösenordsåterställning.

- **Composer**  
  Används för att hantera PHP-bibliotek och beroenden som PHPMailer.


## Last Update
## Senaste uppdateringarna
- **Support-sida**: Nu finns en support-sida som visar status för problem och lösningar.
- **Underhållsläge**: Administratörer kan nu planera och ta bort underhållsperioder via adminpanelen.
- **Bokningssystem**: Underhållsstatus har lagts till för att visa när systemet är nere, och automatiska e-postmeddelanden skickas om bokningar påverkas av underhåll.
