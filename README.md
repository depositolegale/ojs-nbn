OJS NBN:IT
===========
Plugin per l'assegnazione automatica di identificatori [urn:nbn](http://www.depositolegale.it/national-bibliography-number/) nel namespace NBN:IT agli articoli pubblicati con [Open Journal Systems](http://pkp.sfu.ca/?q=ojs).

Funzionalita'
------------
1. Aggiunge due tabelle al db per permettere la registrazione degli NBN;
2. Crea un'interfaccia di registrazione degli NBN accessibile all'amministratore della rivista;
3. Modifica il template della pagina di presentazione dell'articolo e dell'interfaccia oai-pmh per visualizzare i NBN registrati.

Installazione  
-------------
L'installazione prevede due step:
1. Lanciare lo script di creazione delle tabelle *nbn_journal_subnamespace* e *nbn_assigned_string*:
2. utilizzare la funzione OJS di installazione plugin per caricare i file su server


	CREATE TABLE nbn_journal_subnamespace
	(
	  journal_id bigint NOT NULL,
	  subnamespace character varying(255),
	  CONSTRAINT nbn_journal_subnamespace_pkey PRIMARY KEY (journal_id )
	)
	WITH (
	  OIDS=FALSE
	);

	CREATE TABLE nbn_assigned_string
	(
	  article_id bigint NOT NULL,
	  journal_id bigint NOT NULL,
	  assigned_string character varying(255),
	  CONSTRAINT nbn_assigned_string_pkey PRIMARY KEY (article_id )
	)
	WITH (
	  OIDS=FALSE
	);

Credenziali
-----------
Le credenziali di autenticazione al webservice vengono rilasciate a seguito dell'adesione al servizio [MD](http://www.depositolegale.it/nbn-flusso-di-lavoro/)

Licenza
-------
Il plugin e' sviluppato da [CILEA](http://www.cilea.it) ed e' rilasciato sotto [GNU General Public License v2](http://www.gnu.org/licenses/gpl-2.0.html)
