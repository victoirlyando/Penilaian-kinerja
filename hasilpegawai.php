<?php
require_once('includes/init.php');

$user_role = get_role();
if($user_role == 'admin' || $user_role == 'user') {

$page = "Hasilpegawai";
require_once('template/header.php');
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-chart-area"></i> Data Hasil Akhir</h1>
	
	<a href="cetakpegawai.php" target="_blank" class="btn btn-primary"> <i class="fa fa-print"></i> Cetak Data </a>
</div>

<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Hasil Akhir Perankingan</h6>
    </div>

    <div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th>Nama Pegawai</th>
						<th>Nilai Qi</th>
						<th width="15%">Rank</th>
						<!-- <th width="25%">Keterangan</th> -->
						
				</thead>
				<tbody>
					<?php 
                    $pegawai  = $_SESSION['user_id'];
						$no=0;
						$query = mysqli_query($koneksi,"SELECT * FROM hasil JOIN alternatif ON hasil.id_alternatif=alternatif.id_alternatif JOIN user ON alternatif.id_user = user.id_user Where user.id_user = '$pegawai' ORDER BY hasil.nilai ASC");
						while($data = mysqli_fetch_array($query)){
						$no++;
					
					?>
					<tr align="center">
						<td align="left"><?= $data['nama'] ?></td>
						<td><?= $data['nilai'] ?></td>
						<td><?= $no; ?></td>
						<?php 
						// $status = "";
						// $data1 = $data['nilai'];
						// 	if ($data['nilai']>=0 && $data['nilai'] < 0.2500): 
						// 			$status= 'Sangat Bagus';
						// 	elseif ($data['nilai'] >= 0.2500 && $data['nilai'] < 0.5000):
						// 			$status= 'Sudah Bagus';
						// 	elseif ($data['nilai'] >= 0.5000 && $data['nilai'] < 0.7500):
						// 			$status= 'Perlu Ditingkatkan';
						// 	elseif ($data['nilai'] >= 0.7500 && $data['nilai'] < 1):
						// 			$status= 'Perlu Ditindak Lanjuti';
						// 	else:
						// 		$status = 'Belum Memenuhi Kriteria';
						// 		endif;
						// ?>
						<!-- // <td><b><?php echo $status ?></b><br></td>		  -->
		 
						
					</tr>
					<?php
						}
					?>
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