<?php include 'header.php'; ?>
<?php include 'db.php'; ?>

<?php

  
    //IMPOSTAZIONI PAGINAZIONE
    //Numero di risultati visibili per pagina
    $perPagina = 10;

    //Recupero il numero della pagina se non esiste = 1
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    //Offset quanti record devo saltare per mostrare la pagina attuale
    $offset = ($page - 1) * $perPagina;

    //RECUPERO VALORI DEL FORM DI RICERCA
    //Se l utente ha inserito valori nel form li salvo nelle variabili,se vuoti metto stringa vuota
    
    $nome_cliente = $_GET['nome_cliente'] ?? '';
    $paese        = $_GET['paese'] ?? '';
    $citta        = $_GET['citta'] ?? '';
    $prezzo_max   = $_GET['prezzo_max'] ?? '';
    $data         = $_GET['data'] ?? '';
    $posti        = $_GET['posti'] ?? '';



   
    //COSTRUZIONE DINAMICA DELLA QUERY (ricerca con filtri)
   
    //"WHERE 1=1" mi permette di aggiungere AND dinamicamente senza problemi di sintassi
    $where = "WHERE 1=1";

    //Array dei valori da passare ai placeholder della query
    $params = [];

    //Tipi dei parametri (s = string, i = int, d = double)
    $types = '';



    //FILTRO: Nome o Cognome Cliente
    if ($nome_cliente !== '') {

        //Cerco sia per nome che per cognome, uso LIKE perché può contenere parte del nome
        $where .= " AND (c.nome LIKE ? OR c.cognome LIKE ?)";

        //I parametri vengono aggiunti due volte(per nome e cognome)
        $params[] = "%$nome_cliente%";
        $params[] = "%$nome_cliente%";

        //Tipi dei due parametri 2 stringhe
        $types .= 'ss';
    }



    //FILTRO: Paese
    if ($paese !== '') {

        $where .= " AND d.paese LIKE ?";
        $params[] = "%$paese%";
        $types .= 's';
    }



    //FILTRO: Città
    if ($citta !== '') {

        $where .= " AND d.citta LIKE ?";
        $params[] = "%$citta%";
        $types .= 's';
    }



    //FILTRO: Prezzo massimo
    if ($prezzo_max !== '') {

        $where .= " AND d.prezzo <= ?";
        $params[] = floatval($prezzo_max);
        $types .= 'd';
    }



    //FILTRO: Data prenotazione
    if ($data !== '') {

        $where .= " AND p.data_prenotazione = ?";
        $params[] = $data;
        $types .= 's';
    }



    //FILTRO: Posti disponibili reali
    //ORA i posti disponibili si trovano nella tabella "destinazioni"
    //perché li aggiorniamo dinamicamente nella pagina prenotazioni.php
    if ($posti !== '') {

        //MOSTRO SOLO destinazioni che hanno almeno X posti disponibili
        $where .= " AND d.posti_disponibili >= ?";
        $params[] = intval($posti);
        $types .= 'i';
    }


 
    // CONTEGGIO DEI RISULTATI TOTALI (per paginazione)
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM prenotazioni p
        JOIN clienti c     ON p.id_cliente = c.id
        JOIN destinazioni d ON p.id_destinazione = d.id
        $where
    ");

    //Se ci sono parametri li bindo
    if ($types !== '') $stmt->bind_param($types, ...$params);

    $stmt->execute();

    //Numero totale dei risultati trovati
    $total = $stmt->get_result()->fetch_assoc()['total'];

    //Totale pagine
    $totalPages = ceil($total / $perPagina);



    //QUERY FINALE CHE MOSTRA I RISULTATI
    //Aggiungo LIMIT e OFFSET
    $stmt = $conn->prepare("
        SELECT 
            p.id, 
            c.nome, 
            c.cognome, 
            d.citta, 
            d.paese, 
            d.prezzo, 
            p.data_prenotazione
        FROM prenotazioni p
        JOIN clienti c     ON p.id_cliente = c.id
        JOIN destinazioni d ON p.id_destinazione = d.id
        $where
            ORDER BY p.id DESC
            LIMIT ? OFFSET ?
    ");

    //Aggiungo gli ultimi parametri (limit e offset)
    $params[] = $perPagina;
    $params[] = $offset;

    $types .= "ii";

    //Bind parametri e tipi
    $stmt->bind_param($types, ...$params);

    $stmt->execute();

    //Ottengo i risultati
    $result = $stmt->get_result();

?>



    <h2>Ricerca Prenotazioni</h2>



    <!--FORM DI RICERCA-->
    <form action="" method="GET">

        <div>
            <input type="text" name="nome_cliente" value="<?= htmlspecialchars($nome_cliente) ?>" class="form-control" placeholder="Cliente..">
        </div>

        <div class="mt-2">
            <input type="text" name="paese" value="<?= htmlspecialchars($paese) ?>" class="form-control" placeholder="Paese">
        </div>

        <div class="mt-2">
            <input type="text" name="citta" value="<?= htmlspecialchars($citta) ?>" class="form-control" placeholder="Città..">
        </div>

        <div class="mt-2">
            <input type="number" name="prezzo_max" value="<?= htmlspecialchars($prezzo_max) ?>" class="form-control" placeholder="Prezzo max euro..">
        </div>

        <div class="mt-2">
            <input type="number" name="posti" value="<?= htmlspecialchars($posti) ?>" class="form-control" placeholder="Posti disponibili...">
        </div>

        <div class="mt-2 mb-2">
            <input type="date" name="data" value="<?= htmlspecialchars($data) ?>" class="form-control">
        </div>

        <button class="btn btn-primary mb-2">Cerca</button>

        <a href="ricerca.php" class="btn btn-secondary ms-2 mb-2">Annulla</a>

    </form>



    <!--TABELLA RISULTATI-->
    <table class="table table-striped">

        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Paese</th>
                <th>Città</th>
                <th>Prezzo</th>
                <th>Data</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nome'] . ' ' . $row['cognome'] ?></td>
                    <td><?= $row['paese'] ?></td>
                    <td><?= $row['citta'] ?></td>
                    <td><?= $row['prezzo'] ?> €</td>
                    <td><?= $row['data_prenotazione'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>

    </table>



<?php include 'footer.php'; ?>