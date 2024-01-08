# Star Wars Intro
Questa repo contiene il codice di un sito php+mysql dove gli utenti possono registrarsi, creare e condividere intro personalizzate di Star Wars.

Chi da piccolo non ha adorato quei film? E chi oggi non sogna di poter ricreare la parte iniziale potendo però controllare il testo? 

Questo sito lo rende possibile in maniera semplicissima tramite del css e del javascript basilare: non sono necessari pesanti software di animazione 3d o di video editing, basta un browser.

## Struttura della repo/sito
### Lingua
In ogni file i nomi delle variabili e i commenti (che si spera essere esplicativi) sono in inglese. 
I testi delle pagine che gli utenti possono visualizzare sono invece in italiano.

### Struttura delle cartelle
Nella cartella principale si trovano le pagine (.php) visualizzabili dall'utente o, in generale, invocabili da richieste GET o POST.
- La cartella [utils](./utils) contine numerosi file che possono essere inclusi dalle pagine principali per acquisire funzionalità. In particolare il file [session.php](./utils/session.php) contiene effettua in automatico controlli sulla sessione php e reindirizza alla pagina di login se l'utente non è loggato. Il file [no-warnings.php](./utils/no-warning.php) raccoglie tutte le eccezioni e reindirizza ad una pagina di errore
- La cartella [preload](./preload) contiene, nelle sue sottocartelle, numerosi json e txt contenti i dati sulle intro dei film originali, che possono essere emulate sia in italiano che in inglese
- La cartella [parts](./parts) contiene vari blocchi che saranno poi inclusi in tutte le pagine visualizzate
- La cartella [db](./db) contiene il [file .sql](./db/init.sql "Vai al file") generatore delle tabelle del database
- La cartella [assets](./assets) contiene immagini, audio, font, css e js di tutte le pagine

### Variabili di ambiente
La configurazione di Apache per l'applicativo si trova nel file [.htaccess](./.htaccess "Vai al file"), le variabili da caricare per il corretto funzionamento della repo si trovano nel file [.env](./.env "Vai al file").

La configurazione di apache del sito impedisce la fruizione di questi file tramite HTTP (o HTTPS).

### Creazione database e popolamento di default
Il file [init.sql](./db/init.sql "Vai al file") contiene il codice sql che genera database con nome ```riccardo.ciucci``` e crea al suo interno le tabelle necessarie.

Vi sono degli utenti già inseriti, in particolare si segnalano gli utenti:
- ```admin```, con password ```admin1234```: utente amministratore
- ```utente```, con password ```utente1234```: utente non amministratore
- ```Inattivo1```, ```Inattivo2```, ```Inattivo3```, ```Inattivo4```: utenti che non hanno mai effettuato login

## Crediti
Le immagini vettoriali sono di mia creazione o offerte da [svgrepo.com](https://www.svgrepo.com).
Il logo di Star Wars e la musica sono di proprietà della Disney.
Il codice contenuto in questa repo non può essere utilizzato a fini di lucro.

Questo sito è stato realizzato come progetto per l'esame di Progettazione Web del corso di laurea in Ingegneria Informatica dell'[Università di Pisa](https://www.unipi.it "Sito dell'Università").

Il professore del corso è [Vecchio Alessio](https://vecchio.iet.unipi.it/vecchio/ "Sito web del professore").

Anno Accademico 2023-2024