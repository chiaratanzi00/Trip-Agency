<?php 
    include 'header.php'; 
    include 'db.php'; 


    //Logica per impaginazione
    $perPagina = 5;  // n elementi mostrati per pagina
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $perPagina;





    //LOGICA DI AGGIUNTA
    //chiamata POST che prende il gancio del bottone aggiugi del form, prendendo i valori inseriti nei vari campi
    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['aggiungi'])){

        //Preparo lo stato stmt -> statement 
        $stmt = $conn->prepare("INSERT INTO destinazioni (citta, paese, prezzo, data_partenza, data_ritorno, posti_disponibili) 
                                VALUES  (?, ?, ?, ?, ?, ?)");
        //Binding dei parametri e tipizzo
        $stmt->bind_param("ssdssi", $_POST['citta'], $_POST['paese'], $_POST['prezzo'],$_POST['data_partenza'], $_POST['data_ritorno'], $_POST['posti_disponibili']);
        
        //eseguo lo statement
        $stmt->execute();

        echo "<div class='alert alert-success'>Destinazione Aggiunta!</div>";


    }
    




    //LOGICA DI MODIFICA
    $destinazione_modifica = null;

    if (isset($_GET['modifica'])){


        $res = $conn->query("SELECT * FROM destinazioni WHERE id = " . intval($_GET['modifica']));

        $destinazione_modifica = $res->fetch_assoc();

    }





    //MODIFICA DEL DATO, SALVATAGGIO 
    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salva_modifica'])){

        //PREPARE
        $stmt = $conn->prepare("UPDATE destinazioni SET citta=?, paese=?, prezzo=?, data_partenza=?, data_ritorno=?, posti_disponibili=? WHERE id=?");
        //BINDING
        $stmt->bind_param("ssdssii" ,$_POST['citta'],$_POST['paese'],$_POST['prezzo'],$_POST['data_partenza'],$_POST['data_ritorno'],$_POST['posti_disponibili'],$_POST['id']);
        //ESECUZIONE QUERY
        $stmt->execute();
        //messaggio
        echo "<div class='alert alert-info'>Destinazione Modificata correttamente</div>";
    }





    //CANCELLAZIONE CLIENTE
    if(isset($_GET['elimina'])){

        $id = intval($_GET['elimina']);
        $conn->query("DELETE FROM destinazioni WHERE id = $id");

        echo "<div class='alert alert-info'>Destinazione Cancellata correttamente</div>";
    }

    
 ?>





<h2>Destinazioni</h2>

    <!--Form-->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="POST">

                <?php if($destinazione_modifica): ?>
                
                    <input type="hidden" name="id" value="<?= $destinazione_modifica['id'] ?>">

                <?php endif; ?>

                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Città : </label>
                        
                        <!--con value prendo il valore del campo inserito-->
                        <input type="text" name="citta" class="form-control" placeholder="es.: Milano"
                        
                        
                        value="<?= $destinazione_modifica['citta'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Paese : </label>
                        <input type="text" name="paese" class="form-control" placeholder="es.: Italia" 
                        
                        value="<?= $destinazione_modifica['paese'] ?? ''?>"

                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Prezzo : </label>
                        <input type="number" min="1" name="prezzo" class="form-control" placeholder="" 
                        
                        value="<?= $destinazione_modifica['prezzo'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Data Partenza : </label>
                        <input type="date" name="data_partenza" class="form-control" placeholder="" 
                        
                        value="<?= $destinazione_modifica['data_partenza'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Data Ritorno : </label>
                        <input type="date" name="data_ritorno" class="form-control" placeholder="" 
                        
                        value="<?= $destinazione_modifica['data_ritorno'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Posti disponibili : </label>
                        <input type="number" min ="1" name="posti_disponibili" class="form-control" placeholder="" 
                        
                        value="<?= $destinazione_modifica['posti_disponibili'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    
                    
                    <div class="col-12">
                        
                        <button 
                            name="<?= $destinazione_modifica ? 'salva_modifica' : 'aggiungi' ?>" 
                            class="btn <?= $destinazione_modifica ? 'btn-warning' : 'btn-success' ?>" 
                            type="submit">
                            <?= $destinazione_modifica ? 'Salva' : 'Aggiungi' ?>
                        </button>

                        <!--Pulsante ANNULLA-->
                        <?php if ($destinazione_modifica) : ?>

                            <a href="destinazioni.php" class="btn btn-secondary ms-2">Annulla</a>

                        <?php endif;?>
                    
                    </div>

                </div>
            </form>
        </div>
    </div>



    <!--LOGICA RENDER -->
    <?php

        //vado a conteggiare il totale dei clienti con query
        $total = $conn->query("SELECT COUNT(*) as t FROM destinazioni")->fetch_assoc()['t'];
        $totalPages = ceil($total / $perPagina); // il numero di pagine della navigazione

        //QUERY PER ordinare i dati in modo DECRESCENTE IMPAGINATI PER valore di "$perPagina" 
        $result = $conn->query("SELECT * FROM destinazioni ORDER BY id ASC LIMIT $perPagina OFFSET $offset");

    ?>





    <!--Tabella-->
    <div class="table-responsive">
        <table class="table table-striped">

            <thead>
                <!--Intestazione tabella-->
                <tr>

                    <th>ID</th>
                    <th>Città</th>
                    <th>Paese</th>
                    <th>Prezzo</th>
                    <th>Data di Partenza</th>
                    <th>Data di Ritorno</th>
                    <th class="text-center">Posti Disponibili</th>
                    <th class="text-center">Azioni</th>

                </tr>

            </thead>
            <!--Corpo tabella-->
            <tbody>

                <?php while ($row = $result->fetch_assoc()) : ?>
                    
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['citta'] ?></td>
                        <td><?= $row['paese'] ?></td>
                        <td><?= $row['prezzo'] ?></td>
                        <td><?= $row['data_partenza'] ?></td>
                        <td><?= $row['data_ritorno'] ?></td>
                        <td class="text-center"><?= $row['posti_disponibili'] ?></td>
                        <td class="text-center">

                            <a class="btn btn-sm btn-warning" href="?modifica=<?= $row['id']  ?>"><i class="bi bi-pen"></i></a>
                            <a class="btn btn-sm btn-danger" href="?elimina=<?= $row['id']  ?>" onclick="return confirm ('Sicuro?')"><i class="bi bi-trash"></i></a>


                        </td>
                    </tr>


                <?php endwhile; ?>

            </tbody>

        </table>
    </div>



    <!--Paginazione-->
    <nav>

        <ul class="pagination pagination_personal">

            <?php for($i = 1; $i <= $totalPages; $i++ ) : ?>

                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>   

            <?php endfor; ?>



        </ul>
    </nav>

<?php include 'footer.php'; ?>