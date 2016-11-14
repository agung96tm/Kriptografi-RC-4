<!DOCTYPE html>
<html>
<head>
	<title>RC-4</title>
</head>
<body>
<h1>Kripto RC-4</h1>
<form action="index.php" method="post">
	<fieldset>
		<legend>RC-4</legend>
		<div>
			<table>
				<tr>
					<td>Pesan : </td>
					<td><textarea name="pesan" cols="70" rows="4" required="required"></textarea></td>
				</tr>
				<tr>
					<td>Kunci : </td>
					<td><input type="text" name="kunci" required="required"></td>
				</tr>
				<tr>
					<td>Proses : </td>
					<td><select name="proses">
							<option value="E">Enkripsi</option>
							<option value="D">Dekripsi</option>
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="submit" value="Lakukan !"></td>
				</tr>
			</table>
		</div>
	</fieldset>
</form>

<?php 

if(isset($_POST["submit"])) {

	// memasukan nilai ke variable
	$pesan = $_POST["pesan"];
	$kunci = $_POST["kunci"];
	$proses = $_POST["proses"];

	// membuat objek
	$obj = new KripRC4;

	// mengatur kunci
	$obj->setKunci($kunci);
	
	// proses yang di lakukan E = Enkript & D = Dekript
	if($proses == "E") {
		// proeses Enkript
		$obj->EDkripsi($pesan,$proses);

	} else {
		// karena hasil enkrip (cipher) tidak dapat diambil karakternya (karakter aneh), maka yang dipakai adalah ascinya saja
		// misal hasil enkrip "ABC" maka yang dipakai hanya asciinya saja "65, 66, 67"

		// mengubah asci menjadi karakter, dimaksudnya hanya untuk menentukan jumah key yang akan digunakan
		// misal "65 66 67" di ubah menjadi "A B C" dimana berarti kunci yang akan dipakai jumlahnya ada 3
		$pesan = $obj->ubahPesan($pesan);

		// Memasukan ascii ke dalam fungsi dalam kelas yang akan digunakan untuk xor
		// kenapa tidak langsung memakai pesan hasil fungsi ubahPesan()
		// jawabanya adalah saat pesan tersebut di ubah menjadi ascii lagi, malah menjadi nilai yang berbeda
		// misal hasilnya di atas "A B C" pas diubah menjadi ascii, malah hasilnya menjadi "76 75 84"
		$obj->pesanASCI(explode(" ",$pesan));

		// proses Dekript
		$obj->EDkripsi($pesan,$proses);
	}
}


class KripRC4 {
	private $kunci;
	private $S;
	private $K;
	private $H;

	// menyimpan ascii asli untuk proses dekript, alasanya sudah di jelaskan di atas
	private $pesanAsli;
	public function pesanASCI($n) {
		$this->pesanAsli = $n;
	}

	// menset kunci yang akan digunakan untuk E/D
	public function setKunci($n) {
		$this->kunci = $n;
	}

	//  mendapatkan nilai kunci
	public function getKunci() {
		return $this->kunci;
	}

	// mengubah nilai ascii menjadi karakter, alasanya sudah di jelaskan di atas
	public function ubahPesan($n) {
		// membagi tiap ascii berdasarkan sepasi " "
		$h = explode(" ",$n);

		$hasil = "";

		for($i = 0 ; $i < count($h) ; $i++) {
			$hasil .= chr($h[$i]);
		}

		// membalikan nilai hasil mengubah ascii menjadi karakter (sudah digabung menjadi string)
		return $hasil;
	}

	// pembuatan arrayS yang berisi $S[0] = 0, $S[1] = 1, dst...
	public function iniArrayS() {
		for($i = 0 ; $i < 255 ; $i++) {
			$S[$i] = $i;
		}

		$this->S = $S;
	}

	// pengisian tiap karater key ke dalam array $K[]
	// dimana bila keynya kurang dari ketetapan (255) maka dilakukan looping untuk keynya
	// misal key : agung
	// maka di simpan ke $K[] seperti : $K[0] = "a", $K[1] = "g", ... , $K[5] = "g", 
	// dan saat di nilai ke 6 : $K[6] = "a", $k[7] = "g" ... dst
	public function iniArrayK() {		
		$key = $this->getKunci();
		$flag = 1;
		for($i = 0 ; $i < 255 ; $i++) {
			$K[$i] = ord($key[$i % strlen($key)]);
		}

		$this->K = $K;
	}

	public function acakSBox() {
		$i = 0 ;
		$j = 0 ;

		$S = $this->S;
		$K = $this->K;
		for($i = 0 ; $i < 255 ; $i++) {
			$j = ($j + $S[$i] + $K[$i]) % 255;
			$n = $S[$i];
			$S[$i] = $S[$j];
			$S[$j] = $n;
		}

		$this->S = $S;
	}

	public function pseudoRandomByte($pesan) {
		$S = $this->S;
		$K = $this->K;

		$i = 0 ; 
		$j = 0 ;

		$Key = array();
		
		for($p = 0 ; $p < strlen($pesan) ; $p++) {

			$i = ($i + 1) % 255;

			$j = ($j + $S[$i]) % 255;

			$n = $S[$i];
			$S[$i] = $S[$j];
			$S[$j] = $n;

			$t = ($S[$i] + $S[$j]) % 255;

			$Key[] =  $S[$t];
		}
		// mendapatkan key hasil pseudoRandomByte
		return $Key;
	}

	// mengubah ascii menjadi karakter
	public function getHasil($n) {
		$arrHasil = array();

		for($i = 0 ; $i < count($n) ; $i++) {
			$arrHasil[$i] = chr($n[$i]);
		}

		return $arrHasil;
	}

	// mengubah karater menjadi binner
	public function ubahBinner($n) {
		$n = decbin($n);

		if(strlen($n) > 8) {
			// bila nilainya lebih dari 8, maka hapus nilai depannya
			$jum = strlen($n) - 8;
			$n = substr($n, $jum, strlen($n));
		} else {
			// bila nilainya kurang dari 8, maka tambah dengan 0 di depannya
			while(strlen($n) % 8 != 0) {
				$n = "0" . $n;
			}	
		}

		// mengembalikan binner dengan jumlah sampai 8 bit
		return $n;
	}

	// menghasilkan hasil dari xor binner karakter dengan key
	public function hasilXorBinner($p,$k) {
		$arrHasil = array();
		for($i = 0 ; $i < strlen($p) ; $i++) {
			if($p[$i] == $k[$i]) {
				$arrHasil[] = "0";
			} else {
				$arrHasil[] = "1";
			}
		}

		// mengubah binner menjadi desimal
		$hasil = bindec(implode($arrHasil));

		// mengembalikan nilai desimal
		return $hasil;
	}

	// proses XOR dengan memasukan pesan, kunci dan status ("E" atau "D")
	public function prosesXOR($pesan,$kunci,$status) {

		$arrPesan = array();
		$arrHasil = array();

		if($status == "E") {
			// Bila enkripsi
			for($i = 0 ; $i < strlen($pesan) ; $i++) {
				$arrPesan[$i] = ord($pesan[$i]);
			}	
		} else {
			// Bila dekript langsung pakai saja nilai yang sudah di simpan di $this->pesanAsli
			$arrPesan = $this->pesanAsli;
		}

		for($i = 0 ; $i < count($arrPesan) ; $i++) {

			// mengubah pesan karakter yang ke $i menjadi binner
			$p = $this->ubahBinner($arrPesan[$i]);

			// mengubah key karakter yang ke $i menjadi binner
			$k = $this->ubahBinner($kunci[$i]);
		
			// melakukan proses xor
			$h = $this->hasilXorBinner($p,$k);

			// hasil di simpan ke array
			$arrHasil[$i] = $h;
		}

		// mengubah ascii menjadi karakter
		$hasil = $this->getHasil($arrHasil);

		// menyimpan dalam bentuk ascii
		$this->H = $arrHasil;
		return $hasil;
	}

	public function cetakHasil($hasil) {
		// untuk yang karakter
		$hasil = implode($hasil);

		// untuk yang ascii
		$n = implode(" ",$this->H);
		?>
			<fieldset>
				<legend>Hasil : </legend>
				<div style="height: 200px">
				<!-- Cetak Karakter -->
					<?= $hasil ?>
				</div>
			</fieldset>
			<fieldset>
				<div style="padding : 10px 0">
				<!-- Cetak ascii -->
					<?= $n ?>
				</div>
			</fieldset>
		<?php
	}

	public function EDkripsi($n,$status) {

		// memanggil semua fungsi yang ada di kelas
		$this->iniArrayS();
		$this->iniArrayK();
		$this->acakSBox();

		// mendapatkan key hasil pseudoRandomByte
		$key_prb = $this->pseudoRandomByte($n);

		// Proses xor key dan pesan berdasarkan status (E atau D)
		$hasil = $this->prosesXOR($n,$key_prb,$status);

		// Mencetak hasil (baik berupa ascii maupun karakter)
		$this->cetakHasil($hasil);
	}
}
?>
</body>
</html>
