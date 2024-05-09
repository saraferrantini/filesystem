<?php

// Connessione al database MySQL
$host = 'localhost';
$dbname = 'fileprova';
$username = 'root';
$password = '';

try {
    // Tentativo di connessione al database
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
     // Gestione dell'errore se la connessione fallisce
    die("Errore di connessione al database: " . $e->getMessage());
}


try {
      // Creazione della tabella "utenti" se non esiste già
    $db->exec("CREATE TABLE IF NOT EXISTS utenti ( /*  exec Questa funzione esegue la query SQL e restituisce il numero di righe interessate dall'operazione,  */
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(255),
                    cognome VARCHAR(255),
                    email VARCHAR(255)
                )");
} catch (PDOException $e) {
     // Gestione dell'errore se la creazione della tabella fallisce
    die("Errore nella creazione della tabella utenti: " . $e->getMessage());
}

try {
     // Inserimento di dati di esempio nella tabella "utenti"
    $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Mario', 'Rossi', 'mario@email.com')");
    $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Luigi', 'Verdi', 'luigi@email.com')");
    $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Giovanna', 'Bianchi', 'giovanna@email.com')");
} catch (PDOException $e) {
    die("Errore nell'inserimento dei dati di esempio: " . $e->getMessage());
}

//------------------- // Esporta i dati in un file CSV con campi DELIMITATI-----------------------------------
// Query per selezionare tutti i record dalla tabella "utenti"
$query = $db->query("SELECT * FROM utenti"); 

$rows = $query->fetchAll(PDO::FETCH_ASSOC);
 /* Il metodo fetchAll(PDO::FETCH_ASSOC) restituisce un array contenente tutti i risultati della query */
$csvFile = fopen('utenti_delimitati.csv', 'w');
/*<---  Viene aperto un file CSV in modalità di scrittura ('w') utilizzando la funzione fopen(). Il nome del file è "utenti_delimitati.csv". */
// Scrittura dei dati nel file CSV con campi delimitati
fputcsv($csvFile, array_keys($rows[0])); 
/* La funzione array_keys($rows[0]) restituisce un array con i nomi delle colonne estratti dal primo record dei risultati */
foreach ($rows as $row) {
    fputcsv($csvFile, $row); 
/* Per ogni record, la funzione fputcsv() viene utilizzata per scrivere i dati nel file CSV. I dati vengono formattati e scritti nel file CSV utilizzando i campi delimitati. */
}
fclose($csvFile);

// ----------------------------Esporta i dati in un file CSV con campi NON DELIMITATI-----------------------------------------
$csvFile = fopen('utenti_nondelimitati.csv', 'w');
foreach ($rows as $row) {
     // Scrittura dei dati nel file CSV con campi non delimitati utilizzando la tabulazione come delimitatore
    fputcsv($csvFile, $row, "\t"); // -------------------------DELIMITATORE DI TABULAZIONE-------------------------
}
fclose($csvFile);

// -----------------------------------------Importa i dati dal file CSV al database-------------------------------------
$csvData = array_map('str_getcsv', file('utenti_delimitati.csv'));
array_walk($csvData, function(&$a) use ($csvData) {
    $a = array_combine($csvData[0], $a);
});
// ----------------------------------------- Quesy per inserire i dati dal file CSV al database-------------------------------------
$query = $db->prepare("INSERT INTO utenti ( nome, cognome, email) VALUES ( :nome, :cognome, :email)");
foreach ($csvData as $row)
 {
  $query->execute(["nome"=>$row["nome"],
    "cognome"=>$row["cognome"],
    "email"=>$row["email"]]); 
}
// -----------------------------------------fine Query per inserire nel database-------------------------------------

// Chiusura della connessione al database
$db = null;

// Stampa un messaggio di completamento
echo "Esportazione e importazione completate con successo!";
?>





<!-- Questo codice PHP gestisce l'interazione con un database MySQL e l'importazione/esportazione dei dati da/a file CSV -->

<!-- 1)CONNESSIONE AL DATABASE: Si connette al database MySQL utilizzando le credenziali fornite (nome utente, password, nome del database). -->
<!-- 2)CREAZIONE TABELLA: Crea una tabella chiamata "utenti" se non esiste già nel database. La tabella ha quattro colonne: id (chiave primaria auto-incrementante), nome, cognome ed email. --> 
<!-- 3)INSERIMENTO DATI DI ESEMPIO: Inserisce tre righe di dati di esempio nella tabella "utenti".-->
<!-- 4)ESPORTAZIONE DATI in un file CSV: Esegue una query per selezionare tutti i record dalla tabella "utenti", quindi scrive i dati in un file CSV. Due file CSV vengono generati: uno con campi delimitati e l'altro con campi non delimitati. -->
<!-- 5)IMPORTAZIONE dei dati da un file CSV al database: Legge i dati dal file CSV "utenti_delimitati.csv", mappa ogni riga con i nomi delle colonne della tabella nel database e esegue una query di inserimento per aggiungere i dati nel database. -->
<!-- 6)Chiusura della connessione al database: Chiude la connessione al database dopo aver completato tutte le operazioni.-->