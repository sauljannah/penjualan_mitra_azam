-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: penjualan_mitra_azam
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga_beli` int(11) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `stok_minimum` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_penjualan` enum('Normal','Kaca') DEFAULT 'Normal',
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (20,'A001','Besi 12 standar',85000,125000,330,30,'2026-05-25','Normal'),(21,'A002','Besi 10 standar',65000,90000,330,30,'2026-05-25','Normal'),(22,'A003','Besi 8 standar',40000,70000,330,30,'2026-05-25','Normal'),(23,'A004','Besi 6 standar',35000,40000,329,30,'2026-05-25','Normal'),(24,'B001','Bendrat /kg',20000,30000,329,30,'2026-05-25','Normal'),(25,'C001','Paku 4-5-7-10-12- cm/kg',20000,30000,330,30,'2026-05-25','Normal'),(26,'C002','Paku 2-3 cm/kg',20000,40000,330,30,'2026-05-25','Normal'),(27,'C003','Paku beton / Dus 5 - 7 - 10',20000,35000,330,30,'2026-05-25','Normal'),(28,'C004','Paku putih 4 perkah 5 - 7 - 10 cm/kg',25000,40000,330,30,'2026-05-25','Normal'),(29,'D001','Zen Putih Gajah Mas  atau Rusa 0,20/L',65000,75000,330,30,'2026-05-25','Normal'),(30,'D002','Zen Spandek 3 m/L',95000,135000,330,30,'2026-05-25','Normal'),(31,'D003','Zen Spandek 4 m/L',120000,169000,330,30,'2026-05-25','Normal'),(32,'D004','Zen Resin Biru/L',65000,75000,330,30,'2026-05-25','Normal'),(33,'D005','Zen Merah/L',65000,75000,330,30,'2026-05-25','Normal'),(34,'D006','Zen Licin/Talang 30 cm/m',15000,20000,330,30,'2026-05-25','Normal'),(35,'D007','Zen Licin/Talang 40 cm/m',18000,25000,330,30,'2026-05-25','Normal'),(36,'D008','Zen Licin/Talang 50 cm/m',20000,30000,330,30,'2026-05-25','Normal'),(37,'E001','Skop Cap Mata',120000,150000,330,30,'2026-05-25','Normal'),(38,'F001','Eksabor Ling 3.5\" /L',95000,105000,330,30,'2026-05-25','Normal'),(39,'F002','Lem Eksabor Raja',100000,125000,330,30,'2026-05-25','Normal'),(40,'F003','Paku Eksabor 2 cm/Dus',20000,40000,330,30,'2026-05-25','Normal'),(41,'G001','Kolset Jongkok KA',230000,300000,10,5,'2026-05-25','Normal'),(42,'G002','Kolset Jongkok AMS',250000,400000,330,30,'2026-05-25','Normal'),(43,'G003','Kaca Blok',35000,45000,30,20,'2026-05-25','Normal'),(44,'G004','Batu Angin Kupu-kupu',100000,70000,39,20,'2026-05-25','Normal'),(45,'G005','Batu Angin Super Minimalis',50000,75000,30,20,'2026-05-25','Normal'),(46,'H001','Piso Plamer',5000,7000,30,20,'2026-05-25','Normal'),(47,'H002','Kuku Kramik 20 cm',4000,5000,30,20,'2026-05-25','Normal'),(48,'H003','Kertas Plas /meter',10000,15000,20,10,'2026-05-25','Normal'),(49,'I001','Tripleks 3\"',60000,75000,30,20,'2026-05-25','Normal'),(50,'I002','Tripleks 5\"',100000,125000,30,20,'2026-05-25','Normal'),(51,'I003','Tripleks 6\"',110000,135000,30,20,'2026-05-25','Normal'),(52,'I004','Tripleks 9\"',130000,165000,30,20,'2026-05-25','Normal'),(53,'I005','Tripleks 12\"',200000,250000,29,20,'2026-05-25','Normal'),(54,'I006','Tripleks 15\"',235000,275000,30,20,'2026-05-25','Normal'),(55,'H004','Plamir Baglion',260000,280000,30,20,'2026-05-25','Normal'),(56,'J001','Krang Air',35000,60000,30,20,'2026-05-25','Normal'),(57,'C005','Paku Seng',25000,35000,330,30,'2026-05-25','Normal'),(58,'J002','Selang Tukang',15000,22000,30,20,'2026-05-28','Normal'),(59,'J003','Selang Air',7000,10000,30,20,'2026-05-28','Normal'),(60,'K001','Kabel 2x1,5 mm2',15000,20000,28,20,'2026-05-28','Normal'),(61,'K002','Kabel 2x2,5 mm',15000,25000,30,20,'2026-05-28','Normal'),(62,'L001','Lem Plamir (fox)',10000,20000,30,20,'2026-05-28','Normal'),(63,'L002','Lem Pipa',7000,10000,30,20,'2026-05-28','Normal'),(64,'F004','Les Plan Eksabor 20',55000,85000,30,20,'2026-05-28','Normal'),(65,'F005','Les Plan Eksabor 30',65000,95000,30,20,'2026-05-28','Normal'),(66,'M001','Seng Licin Lebar 1 meter',65000,95000,30,20,'2026-05-28','Normal'),(67,'A005','Besi Hollow Plapon 4x4',25000,40000,30,20,'2026-05-28','Normal'),(68,'A006','Besi Plapon Hollow 2x4',15000,30000,30,20,'2026-05-28','Normal'),(69,'N001','Karoro',15000,30000,30,20,'2026-05-28','Normal'),(70,'N002','Tali / meter',45000,65000,30,20,'2026-05-28','Normal'),(71,'N003','Semen Tonasa',81000,105000,-3,20,'2026-05-28','Normal'),(72,'P001','Kuas Roll Besar',35000,40000,30,20,'2026-05-28','Normal'),(73,'P002','Kuas Roll Kecil',20000,35000,30,20,'2026-05-28','Normal'),(74,'P003','Mesin Pompa Air (Sumitsu)',950000,1050000,30,20,'2026-05-28','Normal'),(75,'P004','Parlak',15000,25000,30,20,'2026-05-28','Normal'),(76,'Q001','Pipa Kotak Galvanix 2x4',100000,135000,30,20,'2026-05-28','Normal'),(77,'Q002','Pipa Kotak Galvanix 4x4',130000,170000,30,20,'2026-05-28','Normal'),(78,'Q003','Kanal (Vivo)',110000,130000,29,20,'2026-05-28','Normal'),(79,'R001','Ring',45000,65000,30,20,'2026-05-28','Normal'),(80,'R002','Westafel Cuci Piring American Standar 2 Lubang',550000,650000,20,10,'2026-05-28','Normal'),(81,'R003','Westafel Cuci Piring American Standar 1 Lubang',280000,400000,20,10,'2026-05-28','Normal'),(82,'S001','Terpal 2x4',50000,70000,20,10,'2026-05-28','Normal'),(83,'S002','Terpal 4x6',110000,145000,20,10,'2026-05-28','Normal'),(84,'S003','Terpal 6x8',200000,275000,20,10,'2026-05-28','Normal'),(85,'Q004','Pipa Kotak  Galvanix 4x6',130000,190000,30,20,'2026-05-28','Normal'),(86,'S004','Linggis Panjang 110cm',95000,135000,20,10,'2026-05-28','Normal'),(87,'S005','Linggis Panjang 70cm',70000,90000,20,10,'2026-05-28','Normal'),(88,'T001','Gipsun',80000,100000,30,20,'2026-05-28','Normal'),(89,'T002','Lem  Gipsun',100000,115000,30,20,'2026-05-28','Normal'),(90,'T003','Baut 1/2 Panjang x 20cm',12000,18000,30,20,'2026-05-28','Normal'),(91,'T004','Baut 3/4 Panjang x 20cm',10000,16000,30,20,'2026-05-28','Normal'),(92,'T005','Baut 3/4 Panjang x 5cm',8000,14000,30,20,'2026-05-28','Normal'),(93,'U001','Keramik 60x60',215000,250000,30,20,'2026-05-28','Normal'),(94,'U002','Keramik 50x50',76000,150000,30,20,'2026-05-28','Normal'),(95,'U003','Keramik 40x40',57000,130000,30,20,'2026-05-28','Normal'),(96,'U004','Keramik  Sanpawer 40x25',76000,150000,30,20,'2026-05-28','Normal'),(97,'V001','Pipa Air pawwer 4 inc',190000,210000,30,20,'2026-05-28','Normal'),(98,'V002','Pipa Air 3 inc',110000,140000,30,20,'2026-05-28','Normal'),(99,'V003','Pipa Air 2,1/2 inc',120000,130000,30,20,'2026-05-28','Normal'),(100,'V004','Pipa Air 2 inc',85000,100000,30,20,'2026-05-28','Normal'),(101,'V005','Pipa Air 1,1/2 inc',70000,90000,30,20,'2026-05-28','Normal'),(102,'V006','Pipa Air 1 inc',55000,65000,30,20,'2026-05-28','Normal'),(103,'V007','Pipa Air 3/4 inc',40000,55000,30,20,'2026-05-28','Normal'),(104,'V008','Pipa Air 1/2 inc',20000,40000,30,20,'2026-05-28','Normal'),(107,'BBB124','Kaca',350000,400000,5,2,'2026-06-25','Normal');
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barang_masuk`
--

DROP TABLE IF EXISTS `barang_masuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barang_masuk` (
  `id_masuk` int(11) NOT NULL AUTO_INCREMENT,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga_beli` int(11) DEFAULT NULL,
  `harga_jual` int(20) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_masuk`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang_masuk`
--

LOCK TABLES `barang_masuk` WRITE;
/*!40000 ALTER TABLE `barang_masuk` DISABLE KEYS */;
INSERT INTO `barang_masuk` VALUES (6,44,10,100000,70000,'baru masuk','2026-05-28 11:26:59'),(8,107,3,350000,400000,'pt.abadi','2026-06-24 15:08:53');
/*!40000 ALTER TABLE `barang_masuk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer` (
  `id_customer` int(11) NOT NULL AUTO_INCREMENT,
  `nama_customer` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  PRIMARY KEY (`id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penjualan`
--

DROP TABLE IF EXISTS `detail_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_penjualan` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL,
  `kebutuhan` decimal(10,2) DEFAULT 1.00,
  PRIMARY KEY (`id_detail`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` VALUES (63,60,24,1,30000,0,1.00),(64,61,71,2,105000,0,1.00),(65,62,105,1,350000,0,0.00),(66,63,53,1,250000,0,1.00),(67,64,23,1,40000,0,1.00),(68,65,71,1,105000,0,1.00),(69,66,78,1,130000,0,1.00),(70,67,60,1,20000,0,1.00),(71,68,60,1,20000,0,1.00),(72,69,44,1,70000,0,1.00),(73,70,107,1,400000,0,1.00),(74,71,107,1,400000,0,1.00);
/*!40000 ALTER TABLE `detail_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manajemen_user`
--

DROP TABLE IF EXISTS `manajemen_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manajemen_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL,
  `username` varchar(100) NOT NULL,
  `level` enum('admin','kasir') NOT NULL,
  `status` varchar(100) NOT NULL,
  `aksi` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manajemen_user`
--

LOCK TABLES `manajemen_user` WRITE;
/*!40000 ALTER TABLE `manajemen_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `manajemen_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` datetime NOT NULL,
  `total_harga` int(11) NOT NULL,
  `bayar` int(11) NOT NULL,
  `metode_pembayaran` int(100) NOT NULL,
  `referensi` int(20) NOT NULL,
  `kasir` int(20) NOT NULL,
  `nama_kasir` varchar(100) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `kembali` int(11) NOT NULL,
  `nama_customer` varchar(100) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `status_pembayaran` enum('Lunas','Belum Lunas') NOT NULL,
  `bukti_transaksi` varchar(255) NOT NULL,
  `keuntungan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (53,'2026-05-28 00:00:00',360000,400000,0,0,0,'',0,40000,'','0000-00-00','Lunas','',100000,0),(54,'2026-05-28 00:00:00',195000,200000,0,0,0,'',0,5000,'','0000-00-00','Lunas','',44000,0),(55,'2026-05-28 00:00:00',90000,100000,0,0,0,'',0,10000,'','0000-00-00','Lunas','',25000,0),(56,'2026-05-28 00:00:00',210000,250000,0,0,0,'',0,40000,'','0000-00-00','Lunas','',48000,0),(57,'2026-05-28 00:00:00',210000,210000,0,0,0,'',0,0,'','0000-00-00','Lunas','',48000,0),(58,'2026-05-28 00:00:00',210000,210000,0,0,0,'',0,0,'','0000-00-00','Lunas','',48000,0),(59,'2026-06-06 00:00:00',30000,50000,0,0,0,'',0,20000,'','0000-00-00','Lunas','',10000,0),(60,'2026-06-06 00:00:00',30000,50000,0,0,0,'',0,20000,'','0000-00-00','Lunas','',10000,0),(61,'2026-06-06 00:00:00',210000,220000,0,0,0,'',0,10000,'','0000-00-00','Lunas','',48000,0),(62,'2026-06-24 00:00:00',350000,400000,0,0,0,'',0,50000,'','0000-00-00','Lunas','',50000,0),(63,'2026-06-24 00:00:00',250000,300000,0,0,0,'',0,50000,'','0000-00-00','Lunas','',50000,13),(64,'2026-06-24 00:00:00',40000,0,0,0,0,'',0,0,'marlin','0000-00-00','Lunas','',5000,13),(65,'2026-06-24 14:57:26',105000,110000,0,0,0,'',0,5000,'','0000-00-00','Lunas','',24000,13),(66,'2026-06-24 15:05:14',130000,130000,0,0,0,'',0,0,'','0000-00-00','Lunas','',20000,13),(67,'2026-06-24 15:13:51',20000,20000,0,0,0,'',0,0,'','0000-00-00','Lunas','',5000,13),(68,'2026-06-24 15:14:51',20000,0,0,0,0,'',0,0,'marlin','0000-00-00','Lunas','',5000,13),(69,'2026-06-25 08:01:14',70000,0,0,0,0,'',0,0,'marlin','0000-00-00','Lunas','',-30000,13),(70,'2026-06-25 09:00:08',400000,0,0,0,0,'',0,0,'marlin','2026-06-28','Belum Lunas','',50000,13),(71,'2026-06-25 09:03:32',400000,0,0,0,0,'',0,0,'marlin','2026-06-28','Belum Lunas','',50000,13);
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan_detail`
--

DROP TABLE IF EXISTS `penjualan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penjualan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga_jual` int(11) DEFAULT NULL,
  `status_sync` enum('pending','sync') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan_detail`
--

LOCK TABLES `penjualan_detail` WRITE;
/*!40000 ALTER TABLE `penjualan_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `penjualan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `piutang`
--

DROP TABLE IF EXISTS `piutang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `piutang` (
  `id_piutang` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) DEFAULT NULL,
  `id_customer` int(11) DEFAULT NULL,
  `total_piutang` int(11) DEFAULT NULL,
  `sisa_piutang` int(11) DEFAULT NULL,
  `status` enum('Belum Lunas','Lunas') DEFAULT NULL,
  PRIMARY KEY (`id_piutang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piutang`
--

LOCK TABLES `piutang` WRITE;
/*!40000 ALTER TABLE `piutang` DISABLE KEYS */;
/*!40000 ALTER TABLE `piutang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profil_toko`
--

DROP TABLE IF EXISTS `profil_toko`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profil_toko` (
  `id_toko` int(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(200) NOT NULL,
  `jenis_usaha` varchar(200) NOT NULL,
  `alamat` text NOT NULL,
  `telepon` int(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `logo` varchar(300) NOT NULL,
  PRIMARY KEY (`id_toko`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profil_toko`
--

LOCK TABLES `profil_toko` WRITE;
/*!40000 ALTER TABLE `profil_toko` DISABLE KEYS */;
INSERT INTO `profil_toko` VALUES (1,'MITRA AZAM','Toko Bangunan','Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual, Kabupaten Seram Bagian Barat',2147483647,'mitraazam@gmail.com','Sistem kasir modern toko bangunan','logo_1779436160_610.jpeg');
/*!40000 ALTER TABLE `profil_toko` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `id_setting` int(11) NOT NULL AUTO_INCREMENT,
  `tema` varchar(20) NOT NULL DEFAULT 'light',
  `notifikasi_stok` varchar(20) NOT NULL,
  `auto_backup` varchar(20) NOT NULL DEFAULT 'nonaktif',
  `terakhir_backup` datetime DEFAULT NULL,
  PRIMARY KEY (`id_setting`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES (1,'light','aktif','aktif','2026-06-25 22:30:33');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','kasir') NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (12,'saul','admin','','','admin_1782400003_648.jpg','$2y$10$KuqV7jJEe10kj07ZepdYxO8H95vX9VS0y2Oa/oHB.O0KdCxKlnXP2','admin','aktif'),(13,'tes saja','kasir','','','','$2y$10$Avqh41LshoTGerJlV1pUMu4ZPKxBRH9NTYHQ0A5bLoWE/rU58PtAq','kasir','aktif');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-26  0:10:39
