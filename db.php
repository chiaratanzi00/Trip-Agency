<?php
    //CONNESSIONE AL DB MYSQL usando MYSQLI

    //parametri di connessione al database

    $host = "localhost";  //host
    $user = "root";       //utente standard di default -> root
    $password = "";       // non abbiamo inserito nessuna password ( la chiede durante la installazione di XAMPP )
    $database = "tripagency"; //nome db su phpmyadmin

    //creo la connessione
    $conn = mysqli_connect($host, $user, $password, $database);

    //in caso di problemi usare con porta specificata :
    //$conn = mysqli_connect($host, $user, $password, $database, 3306); //o la porta configurata nel vs pc

    //verifico che la connessione funzioni 

    if(!$conn){

        //se la connessione fallisce  stampa un messaggi di errore e termina lo script
        die("Connessione fallita: " . mysqli_connect());
    }

    
?>