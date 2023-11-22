# Simple Domus

## Panoramica
Simple Domus è un progetto open-source sviluppato in PHP utilizzando il framework Symfony.
Questo software agisce come un proxy sicuro per la piattaforma di registro elettronico `DOMUS Scuola on Web`, semplificandone l'accesso e ottimizzando l'esperienza per dispositivi mobili.

### Caratteristiche
L'applicativo non richiede alcun database ed è progettato per essere completamente anonimo e sicuro.

Non vengono registrati o loggati dati di accesso o qualsiasi informazione che transita attraverso l'applicativo.

Né l'autore né gli esecutori del codice possano vedere i dati di accesso.

### Funzionalità

L'applicativo funziona come intermediario tra l'utente e la piattaforma di registro elettronico originale, fornendo un'interfaccia semplificata ed ottimizzata per dispositivi mobili.

### Dichiarazione di non affiliazione

Lo sviluppatore non è affiliato, associato, sponsorizzato, né in alcun modo collegato agli sviluppatori ed ai proprietari della piattaforma di registro elettronico originale.

## Installazione

Il progetto è avviabile tramite docker:

```shell
docker run -d ramin/simple-domus:latest
```

## Licenza
Questo progetto è rilasciato sotto la licenza [MIT](LICENSE).
