<?php 
    include 'header.php'; 
    include 'db.php'; 


    //Logica per impaginazione
    $perPagina = 5;  // n elementi mostrati per pagina
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $perPagina;

    
    //QUERY PER ESTRARRE DATI PER SELECT DROPDOWN Clienti e Destinazioni
    $clienti = $conn->query("SELECT id, nome, cognome FROM clienti");
    $destinazioni = $conn->query("SELECT id, citta, paese, posti_disponibili FROM destinazioni");

    //INIZIALIZZO LA prenotazione_modifica a NULL
    $prenotazione_modifica = null;

    //LOGICA DI AGGIUNTA
    //chiamata POST che prende il gancio del bottone aggiugi del form, prendendo i valori inseriti nei vari campi
    
    //Variabile per errori
    $errore_posti = null;

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['aggiungi'])){

        $id_cliente = intval($_POST['id_cliente']);
        $id_destinazione = intval($_POST['id_destinazione']);
        $acconto = floatval($_POST['acconto']);
        $assicurazione = intval($_POST['assicurazione']);
        $numero_persone = intval($_POST['numero_persone']);

        //Recupero posti disponibili totali
        $stmt = $conn->prepare("SELECT posti_disponibili FROM destinazioni WHERE id = ?");
        $stmt->bind_param("i", $id_destinazione);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if(!$res){
            
            $errore_posti = "Destinazione non trovata.";

        } else {
            
            $posti_disponibili = intval($res['posti_disponibili']);

            if($numero_persone > $posti_disponibili){
                $errore_posti = "Richiesti $numero_persone posti, ma disponibili solo $posti_disponibili.";
            }
        }

        //Se NON ci sono errori → inserisco
        if(!$errore_posti){

            //Inserisco prenotazione
            $stmt = $conn->prepare("
                INSERT INTO prenotazioni 
                (id_cliente, id_destinazione, acconto, assicurazione, numero_persone)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiidi", 
                $id_cliente,
                $id_destinazione,
                $acconto,
                $assicurazione,
                $numero_persone
            );
            $stmt->execute();

            //Aggiorno posti
            $stmt = $conn->prepare("
                UPDATE destinazioni 
                SET posti_disponibili = posti_disponibili - ?
                WHERE id = ?
            ");

            $stmt->bind_param("ii", $numero_persone, $id_destinazione);
            $stmt->execute();

            echo "<div class='alert alert-success'>Prenotazione aggiunta! Posti aggiornati.</div>";
            echo "<script>

                    setTimeout(()=>{
                            window.location.href='prenotazioni.php'
                        },2000)

                 </script>";
        }
    }

       





    //LOGICA DI MODIFICA
    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salva_modifica'])){

        $id = intval($_POST['id']);
        $id_cliente = intval($_POST['id_cliente']);
        $id_destinazione = intval($_POST['id_destinazione']);
        $acconto = floatval($_POST['acconto']);
        $assicurazione = intval($_POST['assicurazione']);
        $numero_persone_nuovo = intval($_POST['numero_persone']);

        //Prendo i vecchi dati
        $old = $conn->query("SELECT * FROM prenotazioni WHERE id = $id")->fetch_assoc();
        $numero_persone_vecchio = $old['numero_persone'];
        $id_destinazione_vecchio = $old['id_destinazione'];

        //Se cambio destinazione, restituisco prima i posti alla vecchia
        if($id_destinazione_vecchio != $id_destinazione){
            //Restituisco i posti alla vecchia destinazione
            $conn->query("
                UPDATE destinazioni
                SET posti_disponibili = posti_disponibili + $numero_persone_vecchio
                WHERE id = $id_destinazione_vecchio
            ");

            //Controllo la ridisponibilità nella nuova destinazione
            $res = $conn->query("SELECT posti_disponibili FROM destinazioni WHERE id = $id_destinazione")->fetch_assoc();
            
            if($numero_persone_nuovo > $res['posti_disponibili']){

                $errore_posti = "Nella nuova destinazione non ci sono abbastanza posti.";

            } else {

                //Sottraggo sulla nuova destinazione
                $conn->query("
                    
                    UPDATE destinazioni
                    SET posti_disponibili = posti_disponibili - $numero_persone_nuovo
                    WHERE id = $id_destinazione
                
                ");
            }

        } else {
            //Stessa destinazione, controllo variazione persone
            $diff = $numero_persone_nuovo - $numero_persone_vecchio;

            if($diff > 0){

                $res = $conn->query("
                    
                        SELECT posti_disponibili FROM destinazioni WHERE id = $id_destinazione
                
                    ")->fetch_assoc();

                if($diff > $res['posti_disponibili']){
                    
                    $errore_posti = "Non ci sono abbastanza posti per aumentare le persone.";
               
                }else {
                   
                    $conn->query("
                        UPDATE destinazioni
                        SET posti_disponibili = posti_disponibili - $diff
                        WHERE id = $id_destinazione
                    ");
                }

            } else if($diff < 0){
                
                //Sto diminuendo i posti e li restituisco
                $diffPositiva = abs($diff);

                $conn->query("
                   
                    UPDATE destinazioni
                    SET posti_disponibili = posti_disponibili + $diffPositiva
                    WHERE id = $id_destinazione
                
                ");
            }
        }

        if(!$errore_posti){
            
            $stmt = $conn->prepare("
                UPDATE prenotazioni 
                SET id_cliente=?, id_destinazione=?, acconto=?, assicurazione=?, numero_persone=?
                WHERE id=?
            ");
            
            $stmt->bind_param("iiidii",
                $id_cliente,
                $id_destinazione,
                $acconto,
                $assicurazione,
                $numero_persone_nuovo,
                $id
            );
           
            $stmt->execute();

            echo "<div class='alert alert-info'>Prenotazione modificata!</div>";
            echo "<script>
                        setTimeout(()=>{
                            window.location.href='prenotazioni.php'
                        },2000)

                </script>";
        }
    }





    //CANCELLAZIONE PRENOTAZIONE
    if(isset($_GET['elimina'])){

        $id = intval($_GET['elimina']);

        //Recupero prenotazione
        $p = $conn->query("SELECT * FROM prenotazioni WHERE id = $id")->fetch_assoc();

        //Restituisco posti
        $conn->query("
            UPDATE destinazioni
            SET posti_disponibili = posti_disponibili + {$p['numero_persone']}
            WHERE id = {$p['id_destinazione']}
        ");

        //Elimino prenotazione
        $conn->query("DELETE FROM prenotazioni WHERE id = $id");

        echo "<div class='alert alert-info'>Prenotazione eliminata. Posti ripristinati.</div>";
    }





  
    //LISTA PAGINATA PRENOTAZIONI
    $total = $conn->query("SELECT COUNT(*) as t FROM prenotazioni")->fetch_assoc()['t'];
    $totalPages = ceil($total / $perPagina);

    $stmt = $conn->prepare("
        
        SELECT p.id, c.nome, c.cognome, d.citta, d.paese, 
            p.data_prenotazione, p.acconto, p.assicurazione, p.numero_persone
        FROM prenotazioni p
        JOIN clienti c ON p.id_cliente = c.id
        JOIN destinazioni d ON p.id_destinazione = d.id
        ORDER BY p.id DESC
        LIMIT ? OFFSET ?
    
        ");
    
    $stmt->bind_param("ii", $perPagina, $offset);
    
    $stmt->execute();
    
    $result = $stmt->get_result();

?>





<h2>Prenotazioni</h2>

    <!--Form-->
    <div class="card mb-4">
        <div class="card-body">


            <form action="" method="POST">


                <!--Alert posti prenotazione-->
                <?php if(isset($errore_posti)): ?>

                    <div class="alert alert-danger mb-3">
                        <?= $errore_posti ?>
                    </div>
                    
                <?php endif; ?>

                <!--ID NASCOSTO-->
                <?php if($prenotazione_modifica): ?>
                
                    <input type="hidden" name="id" value="<?= $prenotazione_modifica['id'] ?>">

                <?php endif; ?>




                <div class="row g-3">
                    
                    <div class="col-md-6">

                        <label style="font-weight: 600;" for="">Cliente: </label>
                        <select name="id_cliente" class="form-select" required>

                            <option value="">Seleziona il cliente</option>
                            <?php while ($c = $clienti->fetch_assoc()) : ?>

                                <option value="<?= $c['id'] ?>"

                                    <?= ($prenotazione_modifica && $prenotazione_modifica['id_cliente'] == $c['id']) ? 'selected' : '' ?>>
                                    <?= $c['nome'] . ' ' . $c['cognome'] ?>
                                    
                                </option>

                            <?php endwhile; ?>
                        </select>
                        
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Destinazione: </label>
                        <div class="">
                            <select name="id_destinazione" class="form-control" onfocus='this.size=3;' onblur='this.size=1;' onchange='this.size=1; this.blur();' required>

                                <option value="">Seleziona Destinazione</option>
                                <?php while ($d = $destinazioni->fetch_assoc()) : ?>

                                     <option value="<?= $d['id'] ?>"

                                        <?= ($prenotazione_modifica && $prenotazione_modifica['id_destinazione'] == $d['id']) ? 'selected' : '' ?>>
                                        <?= $d['citta'] . ' ' . $d['paese'] ?>
                                    
                                    </option>


                                <?php endwhile; ?>
                            </select>
                        </div>    


                    </div>
                    
                    <!--Numero persone per la prenotazione-->
                    <div class="col-md-4">
                        <label style="font-weight: 600;">Persone:</label>
                        <input type="number" name="numero_persone" min="1" class="form-control" required>
                    </div>

                   
                    
             

                     <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Acconto: </label>
                        <input type="number" name="acconto" class="form-control" placeholder="" 
                        
                        value="<?= $prenotazione_modifica['acconto'] ?? ''?>"
                        
                        required>
                    </div>

                
                    


                    <div class="col-md-2">
                        <label style="font-weight: 600;" for="">Assicurazione: </label>
                        
                        <!--Logica ternaria dato assicurazione booleano/ tinyInt su Mysql trattato come int in php-->
                        
                        <select name="assicurazione" id="" class="form-select" required>

                            <option value="1"<?= ($prenotazione_modifica && $prenotazione_modifica['assicurazione']) == 1 ? 'selected' : '' ?>>SI</option>
                            <option value="0"<?= ($prenotazione_modifica && $prenotazione_modifica['assicurazione']) == 0 ? 'selected' : '' ?>>NO</option>

                        </select>
                        
                      
                    </div>
                    
                    
                    <div class="col-md-12">
                        
                        <!--Pulsante AGGIUNGI-->
                        <button 
                            name="<?= $prenotazione_modifica ? 'salva_modifica' : 'aggiungi' ?>" 
                            class="btn <?= $prenotazione_modifica ? 'btn-warning' : 'btn-success' ?>" 
                            type="submit">
                            <?= $prenotazione_modifica ? 'Salva' : 'Aggiungi' ?>
                        </button>

                        <!--Pulsante ANNULLA-->
                        <?php if ($prenotazione_modifica) : ?>

                            <a href="prenotazioni.php" class="btn btn-secondary ms-2">Annulla</a>

                        <?php endif;?>
                    </div>

                </div>
            </form>
        </div>
    </div>




    <!--Tabella-->
    <div class="table-responsive">
        <table class="table table-striped">

            <thead>
                <!--Intestazione tabella-->
                <tr>

                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Destinazione</th>
                    <th>Data di Prenotazione</th>
                    <th>Persone</th>
                    <th>Acconto</th>
                    <th>Assicurazione</th>
                    <th class="text-center">Azioni</th>

                </tr>

            </thead>
            <!--Corpo tabella-->
            <tbody>

                <?php while ($row = $result->fetch_assoc()) : ?>
                    
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nome'] . ' ' . $row['cognome'] ?></td>
                        <td><?= $row['citta'] ?></td>
                        <td><?= $row['data_prenotazione'] ?></td>
                        <td><?= $row['numero_persone'] ?></td>
                        <td><?= $row['acconto'] ?></td>
                        <td><?= $row['assicurazione'] == 1 ? 'Presente' : 'Non presente' ?></td>
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


            <?php for($i=1;$i<=$totalPages;$i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>



        </ul>
    </nav>

<?php include 'footer.php'; ?>