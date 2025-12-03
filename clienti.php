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


        $upload = null;

        //controllo del campo documento -> se il file è stato effettivamente caricato
        //$_FILES -> contiene tutti i file caricati tramite l input
        //$_FILES['documento']['name'] -> il nome originale del file scelto dall utente
        //if(!empty($_FILES['documento']['name'])){ -> "solo se un file è stato caricato"
        //quindi se il campo è vuoto non si tenta l UPLOAD

        if(!empty($_FILES['documento']['name'])){

            //ESTRAE IL NOME DEL FILE SENZA PERCORSO
            //   "../../nome_file.estensione"   -> "nome_file.estensione"
            $upload = time() . "_" . basename($_FILES['documento']['name']); //filename
            //sposto il file dalla posizione tmp/ alla cartella uploads/
            move_uploaded_file($_FILES['documento']['tmp_name'], "uploads/" . $upload);

        }

       


        //Preparo lo stato stmt -> statement 
        $stmt = $conn->prepare("INSERT INTO clienti (nome, cognome, email, telefono, nazione, codice_fiscale, documento) 
                                VALUES  (?, ?, ?, ?, ?, ?, ?)");
        //Binding dei parametri e tipizzo
        $stmt->bind_param("sssssss", $_POST['nome'], $_POST['cognome'], $_POST['email'], $_POST['telefono'], $_POST['nazione'], $_POST['codice_fiscale'], $upload);
        
        //eseguo lo statement
        $stmt->execute();

        echo "<div class='alert alert-info'>Cliente Aggiunto!</div>";
        echo "
        
                <script>

                    setTimeout(function () {

                        window.location.href = 'clienti.php'

                    }, 2500);

                </script>
        
             ";

        exit;


    }
    




    //LOGICA DI MODIFICA
    $cliente_modifica = null;

    if (isset($_GET['modifica'])){


        $res = $conn->query("SELECT * FROM clienti WHERE id = " . intval($_GET['modifica']));

        $cliente_modifica = $res->fetch_assoc();

    }





    //MODIFICA DEL DATO, SALVATAGGIO 
    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salva_modifica'])){



        if(!empty($_FILES['documento']['name'])){

            //ESTRAE IL NOME DEL FILE SENZA PERCORSO
            //   "../../nome_file.estensione"   -> "nome_file.estensione"
            $nuovoDocumento = time() . "_" . basename($_FILES['documento']['name']); //filename
            //sposto il file dalla posizione tmp/ alla cartella uploads/
            move_uploaded_file($_FILES['documento']['tmp_name'], "uploads/" . $nuovoDocumento);

        }else{


            $nuovoDocumento = $_POST['documento_esistente'];

        }







        //PREPARE
        $stmt = $conn->prepare("UPDATE clienti SET nome=?, cognome=?, email=?, telefono=?, nazione=?, codice_fiscale=?, documento=? WHERE id=?");
        //BINDING
        $stmt->bind_param("sssssssi" ,$_POST['nome'],$_POST['cognome'],$_POST['email'],$_POST['telefono'],$_POST['nazione'],$_POST['codice_fiscale'],$nuovoDocumento, $_POST['id']);
        //ESECUZIONE QUERY
        $stmt->execute();
        //messaggio
        echo "<div class='alert alert-info'>Cliente modificato correttamente</div>";
        echo "
        
                <script>

                    setTimeout(function () {

                        window.location.href = 'clienti.php'

                    }, 2500);

                </script>
        
             ";

        exit;
    }





    //CANCELLAZIONE CLIENTE
    if(isset($_GET['elimina'])){

        $id = intval($_GET['elimina']);
        $conn->query("DELETE FROM clienti WHERE id = $id");

        echo "<div class='alert alert-info'>Cliente Cancellato correttamente</div>";
    }

    
 ?>





<h2>Clienti</h2>

    <!--Form-->
    <div class="card mb-4">
        <div class="card-body">

            <!--EncType è il supporto dell upload del file nel form-->
            <form action="" method="POST" enctype="multipart/form-data">

                <?php if($cliente_modifica): ?>
                
                    <input type="hidden" name="id" value="<?= $cliente_modifica['id'] ?>">

                <?php endif; ?>

                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Nome : </label>
                        
                        <!--con value prendo il valore del campo inserito-->
                        <input type="text" name="nome" class="form-control" placeholder="es.: Mario"
                        
                        
                        value="<?= $cliente_modifica['nome'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Cognome : </label>
                        <input type="text" name="cognome" class="form-control" placeholder="es.: Rossi" 
                        
                        value="<?= $cliente_modifica['cognome'] ?? ''?>"

                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Email : </label>
                        <input type="text" name="email" class="form-control" placeholder="es.: mario.rossi@mail.it" 
                        
                        value="<?= $cliente_modifica['email'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Telefono : </label>
                        <input type="text" name="telefono" class="form-control" placeholder="es.: 393406587398" 
                        
                        value="<?= $cliente_modifica['telefono'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Nazione : </label>
                        <input type="text" name="nazione" class="form-control" placeholder="es.: Italia" 
                        
                        value="<?= $cliente_modifica['nazione'] ?? ''?>"
                        
                        required>
                    </div>
                    
                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Codice Fiscale : </label>
                        <input type="text" name="codice_fiscale" class="form-control" placeholder="Codice Fiscale di 16 cifre..." 
                        
                        value="<?= $cliente_modifica['codice_fiscale'] ?? ''?>"
                        
                        required>
                    </div>
                    

                    <!--Ottengo il VECCHIO FILE DAL DATABASE-->
                    <?php if ($cliente_modifica) : ?>

                        <input type="hidden" name="documento_esistente" value="<?= $cliente_modifica['documento'] ?>">

                    <?php endif; ?>



                    <div class="col-md-6">
                        <label style="font-weight: 600;" for="">Documento : </label>
                        <input type="file" name="documento" class="form-control" placeholder="Inserisci il codice del documento del cliente..." 
                        
                        value="<?= $cliente_modifica['documento'] ?? ''?>"
                        >

                    </div>
                    
                    <div class="col-12">
                        
                        <button 
                            name="<?= $cliente_modifica ? 'salva_modifica' : 'aggiungi' ?>" 
                            class="btn <?= $cliente_modifica ? 'btn-warning' : 'btn-success' ?>" 
                            type="submit">
                            <?= $cliente_modifica ? 'Salva' : 'Aggiungi' ?>
                        </button>


                        <!--Pulsante ANNULLA-->
                        <?php if ($cliente_modifica) : ?>

                            <a href="clienti.php" class="btn btn-secondary ms-2">Annulla</a>

                        <?php endif;?>
                    
                    </div>

                </div>
            </form>
        </div>
    </div>



    <!--LOGICA RENDER -->
    <?php

        //vado a conteggiare il totale dei clienti con query
        $total = $conn->query("SELECT COUNT(*) as t FROM clienti")->fetch_assoc()['t'];
        $totalPages = ceil($total / $perPagina); // il numero di pagine della navigazione

        //QUERY PER ordinare i dati in modo DECRESCENTE IMPAGINATI PER valore di "$perPagina" 
        $result = $conn->query("SELECT * FROM clienti ORDER BY id ASC LIMIT $perPagina OFFSET $offset");

    ?>






    <!--Tabella-->
    <div class="table-responsive">
        <table class="table table-striped">

            <thead>
                <!--Intestazione tabella-->
                <tr>

                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Nazione</th>
                    <th>Codice Fiscale</th>
                    <th>Documento</th>
                    <th class="text-center">Azioni</th>

                </tr>

            </thead>
            <!--Corpo tabella-->
            <tbody>

                <?php while ($row = $result->fetch_assoc()) : ?>
                    
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nome'] ?></td>
                        <td><?= $row['cognome'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['telefono'] ?></td>
                        <td><?= $row['nazione'] ?></td>
                        <td><?= $row['codice_fiscale'] ?></td>
                        <td>
                            <?php if(!empty($row['documento'])) : ?>
                        
                                <a href="uploads/<?= $row['documento'] ?>"
                                
                                    download
                                    data-bs-toggle="tooltip"
                                    title="Scarica : <?= $row['documento'] ?>">

                                    <i class="bi bi-file-earmark-check"style="font-size: 1.5rem;"></i>

                                </a>
                            
                        
                            <?php else: ?>
                                
                                <span><i class="bi bi-file-x-fill" style="font-size: 1.5rem;"></i></span>


                            <?php endif; ?>

                        </td>
                        
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

        <ul class="pagination  pagination_personal">

            <?php for($i = 1; $i <= $totalPages; $i++ ) : ?>

                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>   

            <?php endfor; ?>



        </ul>
    </nav>

<?php include 'footer.php'; ?>