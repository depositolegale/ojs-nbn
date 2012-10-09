OJS NBN:IT
===========
Plugin per l'assegnazione automatica di identificatori [urn:nbn](http://www.depositolegale.it/national-bibliography-number/) nel namespace NBN:IT agli articoli pubblicati con [Open Journal Systems](http://pkp.sfu.ca/?q=ojs).

Funzionalita'
-------------
1. Aggiunge due tabelle al db per permettere la registrazione degli NBN;
2. Crea un'interfaccia di registrazione degli NBN accessibile all'amministratore della rivista;
3. Stabilisce un dialogo con le API di Magazzini Digitali. Prima di essere abilitati all'utilizzo della API deve essere inoltrata richiesta di abilitazione di un account di accesso al servizio NBN (v sotto).
3. Modifica il template della pagina di presentazione dell'articolo e dell'interfaccia oai-pmh per visualizzare i NBN registrati.

Requisiti di sistema
--------------------
1. OJS versione 2.4.0 o superiore
2. Estensione PHP 'curl' installata
3. Estensione PHP 'dom' installata 

Installazione  
-------------
L'installazione prevede due step:
1. Apportare le modifiche al DB descritte nel file schema.xml
2. Caricare i file sul server utilizzando l'apposito modulo di OJS dedicato all'installazione dei plugin (il plugin deve essere in formato compresso .tar.gz)

Credenziali
-----------
Le credenziali di autenticazione al webservice vengono rilasciate a seguito dell'adesione al servizio [MD](http://www.depositolegale.it/nbn-flusso-di-lavoro/)

Licenza
-------
Il plugin e' sviluppato da [CILEA](http://www.cilea.it) ed e' rilasciato sotto [GNU General Public License v2](http://www.gnu.org/licenses/gpl-2.0.html)
