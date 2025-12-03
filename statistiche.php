<?php 

      include 'header.php'; 
      include 'db.php'; 

?>



<h2>Statistiche</h2>


<form action="" method="GET" class="row g-3 mb-4">

    <div class="col-md-3">
        <label for="">Anno</label>
            <select name="anno" id="" class="form-select">
                <option value="">Scegli</option>
            </select>
    </div>

    <div class="col-md-3">
        <label for="">Destinazione</label>
            <select name="destinazione" id="" class="form-select">
                <option value="">Tutte</option>
            </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary">Aggiorna</button>
    </div>

    <div class="col-md-3 d-flex align-items-end justify">
        <a href="statistiche.php" class="btn btn-outline-success">Esporta dati in CSV</a>
    </div>


</form>



<div class="row">

    <div class="col-md-6 mb-4 mt-4">
        <div class="card p-3">
            <h5 class="text-center">Prenotazioni per mese</h5>

            <!--Chart a linee-->
            <canvas id="lineaPrenotazioni"></canvas>
            
            <button class="btn btn-sm btn-outline-secondary mt-3">
                Scarica PNG
            </button>
        </div>


    </div>

    <div class="col-md-6 mb-4 mt-4">
        <div class="card p-3">
            <h5 class="text-center">Entrate Mensili</h5>

            <!--Chart a barre-->
            <canvas id="barEntrate"></canvas>
            
            <button class="btn btn-sm btn-outline-secondary mt-3">
                Scarica PNG
            </button>
        </div>
    </div>


</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const ctx = document.getElementById('lineaPrenotazioni');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: '# of Votes',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>


<script>
  const ctx2 = document.getElementById('barEntrate');

  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: '# of Votes',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>


<?php include 'footer.php'; ?>