<?php
require_once('includes/init.php');

$user_role = get_role();
if($user_role == 'admin') {

$page = "Perhitungan";
require_once('template/header.php');

mysqli_query($koneksi,"TRUNCATE TABLE hasil;");

$kriterias = array();
$q1 = mysqli_query($koneksi,"SELECT * FROM kriteria ORDER BY kode_kriteria ASC");			
while($krit = mysqli_fetch_array($q1)){
	$kriterias[$krit['id_kriteria']]['id_kriteria'] = $krit['id_kriteria'];
	$kriterias[$krit['id_kriteria']]['kode_kriteria'] = $krit['kode_kriteria'];
	$kriterias[$krit['id_kriteria']]['nama'] = $krit['nama'];
	$kriterias[$krit['id_kriteria']]['bobott'] = $krit['bobot'];
	$kriterias[$krit['id_kriteria']]['ada_pilihan'] = $krit['ada_pilihan'];
}

// print_r($kriterias);
$alternatifs = array();
$q2 = mysqli_query($koneksi,"SELECT * FROM alternatif INNER JOIN user ON alternatif.id_user = user.id_user");			
while($alt = mysqli_fetch_array($q2)){
	$alternatifs[$alt['id_alternatif']]['id_alternatif'] = $alt['id_alternatif'];
	$alternatifs[$alt['id_alternatif']]['nama'] = $alt['nama'];
} 

//Matrix Keputusan (X)
$matriks_x = array();
foreach($alternatifs as $alternatif):
	foreach($kriterias as $kriteria):
		
		$id_alternatif = $alternatif['id_alternatif'];
	
		 $id_kriteria = $kriteria['id_kriteria'];
		//  print_r($id_kriteria);
		$q4 = mysqli_query($koneksi,"SELECT nilai FROM penilaian WHERE id_alternatif='$id_alternatif' AND id_kriteria='$id_kriteria'");
			$data = mysqli_fetch_array($q4);
			$nilai = $data['nilai'];
			if ($nilai == 0) {
				echo "<script>
						confirm('Terdapat Data Yang Belum Diisi Penilaiannya.');
						window.location.href = 'list-penilaian.php'; // Ganti dengan URL halaman yang sesuai
					  </script>";// Menghentikan eksekusi script lebih lanjut
			}
		// if($kriteria['ada_pilihan']==1){
		// 	// $q4 = mysqli_query($koneksi,"SELECT sub_kriteria.nilai FROM penilaian JOIN sub_kriteria WHERE penilaian.nilai=sub_kriteria.id_sub_kriteria AND penilaian.id_alternatif='$alternatif[id_alternatif]' AND penilaian.id_kriteria='$kriteria[id_kriteria]'");
		// 	// $data = mysqli_fetch_array($q4);
		// 	// $nilai = $data['nilai'];
			
		// }elseif ($kriteria['ada_pilihan']==2) {
		// 	$q4 = mysqli_query($koneksi,"SELECT nilai FROM penilaian WHERE id_alternatif='$alternatif[id_alternatif]' AND id_kriteria='$kriteria[id_kriteria]'");
		// 	$data = mysqli_fetch_array($q4);
		// 	$nilai = $data['nilai'];
		// }elseif ($kriteria['ada_pilihan']==3) {
		// 	$q4 = mysqli_query($koneksi,"SELECT nilai FROM penilaian WHERE id_alternatif='$alternatif[id_alternatif]' AND id_kriteria='$kriteria[id_kriteria]'");
		// 	$data = mysqli_fetch_array($q4);
		// 	$nilai = $data['nilai'];
		// }else{
		// 	$q4 = mysqli_query($koneksi,"SELECT nilai FROM penilaian WHERE id_alternatif='$alternatif[id_alternatif]' AND id_kriteria='$kriteria[id_kriteria]'");
		// 	$data = mysqli_fetch_array($q4);
		// 	$nilai = $data['nilai'];
		// }

		 $matriks_x[$id_kriteria][$id_alternatif] = $nilai;
		
	endforeach;
endforeach;

//Matriks Normalisasi (X)
$nilai_x = array();
foreach($alternatifs as $alternatif):
    foreach($kriterias as $kriteria):
        $id_alternatif = $alternatif['id_alternatif'];
        $id_kriteria = $kriteria['id_kriteria'];
         $nilai = $matriks_x[$id_kriteria][$id_alternatif];
		//  print_r($nilai);
		// $nilai_max =  max($matriks_x[$id_kriteria]);
		//  print_r($nilai_max);
		//  $nilai_min = min($matriks_x[$id_kriteria]);
		$nilai_max = @(max($matriks_x[$id_kriteria]));
		$nilai_min = @(min($matriks_x[$id_kriteria]));
		 if ($nilai_max - $nilai_min == 0) {
            echo "<script>
                    confirm('Masukkan data pegawai terlebih dahulu.');
                    window.location.href = 'list-alternatif.php'; // Ganti dengan URL halaman yang sesuai
                  </script>";// Menghentikan eksekusi script lebih lanjut
        }
        $x = ($nilai_max - $nilai) / ($nilai_max - $nilai_min); //cek rumus kembali

        $nilai_x[$id_alternatif][$id_kriteria] = round($x, 3);
    endforeach;
endforeach;

//matriks bobot kriteria w belum berhasil
$bobot_kriteria = array();
$sum_w = 0;
$w = array();
$nilai_w = array();
foreach($kriterias as $kriteria):
	// $bobot_kriteria[$kriteria['id_kriteria']] = round($bobot_kriteria[$kriteria['id_kriteria']],3); 
	$bobot_kriteria = $kriteria['bobott'];
	$sum_w += $bobot_kriteria;
	$w = $bobot_kriteria/$sum_w;
	$nilai_w[$bobot_kriteria] = round($w,3);
endforeach;

//Matrix Normalisasi (R)
$nilai_r = array();
$s = array();
$n_s = array();
foreach($alternatifs as $alternatif):
	$total_r = 0;
	foreach($kriterias as $kriteria):
		$id_alternatif = $alternatif['id_alternatif'];
		$id_kriteria = $kriteria['id_kriteria'];
		$bobot = $kriteria['bobott'];
		$nilai = $nilai_x[$id_alternatif][$id_kriteria];
		
		$r = $nilai*$bobot;
			
		$nilai_r[$id_alternatif][$id_kriteria] = round($r,3);
		$total_r += round($r,3);
	endforeach;
	$s[$id_alternatif] = $total_r;
	$n_s[$id_alternatif]['nilai'] = $total_r;
endforeach;

// Nilai R
$r = array();
$n_r = array();
foreach($alternatifs as $alternatif):
	$id_alternatif = $alternatif['id_alternatif'];
		
	$nilai_max = @(max($nilai_r[$id_alternatif]));
		
	$r[$id_alternatif] = $nilai_max;
	$n_r[$id_alternatif]['nilai'] = $nilai_max;
endforeach;

// Max R
$r_nilai = array();
foreach($n_r as $key =>$row):
	$r_nilai[$key] = $row['nilai'];
endforeach;

// Max S
$s_nilai = array();
foreach($n_s as $key =>$row):
	$s_nilai[$key] = $row['nilai'];
endforeach;

//Nilai Qi
$nilai_q = array();
foreach($alternatifs as $alternatif):
	$id_alternatif = $alternatif['id_alternatif'];
	
	$nil_s = $s[$id_alternatif];
	$nil_r = $r[$id_alternatif];
	$max_s = max($s_nilai);
	$min_s = min($s_nilai);
	$max_r = max($r_nilai);
	$min_r = min($r_nilai);
	
	$v = 0.5;
	$n1 = $nil_s-$min_s;
	$n2 = $max_s-$min_s;
	$n3 = $nil_r-$min_r;
	$n4 = $max_r-$min_r;
	
	$bagi1=$n1/$n2;
	$bagi2=$n3/$n4;
	
	$hasil1= $bagi1*$v;
	$hasil2= $bagi2*(1-$v);
	$q = $hasil1+$hasil2;
	// $q =((($nil_s-$min_s)/($max_s-$min_s))*$v)+((($nil_r-$min_r)/($U$24))*$v)
	$nilai_q[$id_alternatif] = round($q,3);
endforeach;
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-calculator"></i> Data Perhitungan</h1>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Matrix Keputusan (X)</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria): ?>
							<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<?php
						foreach ($kriterias as $kriteria):
							$id_alternatif = $alternatif['id_alternatif'];
							$id_kriteria = $kriteria['id_kriteria'];
							echo '<td>';
							echo $matriks_x[$id_kriteria][$id_alternatif];
							echo '</td>';
						endforeach
						?>
					</tr>
					<?php
						$no++;
						endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Normalisasi Matrix (X)</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria): ?>
							<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<?php						
						foreach($kriterias as $kriteria):
							$id_alternatif = $alternatif['id_alternatif'];
							$id_kriteria = $kriteria['id_kriteria'];
							echo '<td>';
							echo $nilai_x[$id_alternatif][$id_kriteria];
							echo '</td>';
						endforeach;
						?>
					</tr>
					<?php
						$no++;
						endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Bobot Kriteria (W)</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php foreach ($kriterias as $kriteria): ?>
						<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php 
						
						foreach ($kriterias as $kriteria): ?>
						<td>
						<?php 
				echo $kriteria['bobott']; //asli
						// echo $w ;
						// echo $nilai_w[$bobot_kriteria] ;
						?>
						</td>
						<?php endforeach ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Normalisasi Bobot (R)</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria): ?>
							<th><?= $kriteria['kode_kriteria'] ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<?php						
						foreach($kriterias as $kriteria):
							$id_alternatif = $alternatif['id_alternatif'];
							$id_kriteria = $kriteria['id_kriteria'];
							echo '<td>';
							echo $nilai_r[$id_alternatif][$id_kriteria];
							echo '</td>';
						endforeach;
						?>
					</tr>
					<?php
						$no++;
						endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Nilai R</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
						<th>R<sub><?= $no ?></sub></th>
						<?php 
						$no++;
						endforeach ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php 
						foreach ($alternatifs as $alternatif): 
						$id_alternatif = $alternatif['id_alternatif'];
						?>
						<td>
						<?php 
						echo $r[$id_alternatif];
						?>
						</td>
						<?php endforeach ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Nilai S</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php 
						$no=1;
						foreach ($alternatifs as $alternatif): ?>
						<th>S<sub><?= $no ?></sub></th>
						<?php 
						$no++;
						endforeach ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php 
						foreach ($alternatifs as $alternatif): 
						$id_alternatif = $alternatif['id_alternatif'];
						?>
						<td>
						<?php 
						echo $s[$id_alternatif];
						?>
						</td>
						<?php endforeach ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Nilai S dan R</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th>S<sup>+</sup></th>
						<th>S<sup>-</sup></th>
						<th>R<sup>+</sup></th>
						<th>R<sup>-</sup></th>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<td><?= max($s_nilai); ?></td>
						<td><?= min($s_nilai); ?></td>
						<td><?= max($r_nilai); ?></td>
						<td><?= min($r_nilai); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Nilai Qi</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%">No</th>
						<th>Alternatif</th>
						<th>Nilai Qi</th>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php 
						$no=1;
						foreach ($alternatifs as $alternatif):
						$id_alternatif = $alternatif['id_alternatif'];
						?>
					<tr align="center">
						<td><?= $no; ?></td>
						<td align="left"><?= $alternatif['nama'] ?></td>
						<td>
						<?php 
						echo $hasil = $nilai_q[$id_alternatif];
						?>
						</td>
					</tr>
					<?php
						$no++;
						mysqli_query($koneksi,"INSERT INTO hasil (id_hasil, id_alternatif, nilai) VALUES ('', '$alternatif[id_alternatif]', '$hasil')");
						endforeach
					?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
require_once('template/footer.php');
}
else {
	header('Location: login.php');
}
?>