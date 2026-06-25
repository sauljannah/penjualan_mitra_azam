-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: penjualan_mitra_azam
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `id_barang` int NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `harga_beli` int NOT NULL,
  `harga_jual` int NOT NULL,
  `stok` int NOT NULL,
  `stok_minimum` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (20,'A001','Besi 12 standar',85000,125000,330,30,'2026-05-25 02:01:50'),(21,'A002','Besi 10 standar',65000,90000,330,30,'2026-05-25 02:02:48'),(22,'A003','Besi 8 standar',40000,70000,330,30,'2026-05-25 02:03:52'),(23,'A004','Besi 6 standar',35000,40000,330,30,'2026-05-25 02:06:44'),(24,'B001','Bendrat /kg',20000,30000,329,30,'2026-05-25 02:09:54'),(25,'C001','Paku 4-5-7-10-12- cm/kg',20000,30000,330,30,'2026-05-25 02:12:37'),(26,'C002','Paku 2-3 cm/kg',20000,40000,330,30,'2026-05-25 02:14:57'),(27,'C003','Paku beton / Dus 5 - 7 - 10',20000,35000,330,30,'2026-05-25 02:17:33'),(28,'C004','Paku putih 4 perkah 5 - 7 - 10 cm/kg',25000,40000,330,30,'2026-05-25 02:20:52'),(29,'D001','Zen Putih Gajah Mas  atau Rusa 0,20/L',65000,75000,330,30,'2026-05-25 02:23:49'),(30,'D002','Zen Spandek 3 m/L',95000,135000,330,30,'2026-05-25 02:25:11'),(31,'D003','Zen Spandek 4 m/L',120000,169000,330,30,'2026-05-25 02:26:11'),(32,'D004','Zen Resin Biru/L',65000,75000,330,30,'2026-05-25 02:27:58'),(33,'D005','Zen Merah/L',65000,75000,330,30,'2026-05-25 02:29:08'),(34,'D006','Zen Licin/Talang 30 cm/m',15000,20000,330,30,'2026-05-25 02:31:04'),(35,'D007','Zen Licin/Talang 40 cm/m',18000,25000,330,30,'2026-05-25 02:32:22'),(36,'D008','Zen Licin/Talang 50 cm/m',20000,30000,330,30,'2026-05-25 02:33:41'),(37,'E001','Skop Cap Mata',120000,150000,330,30,'2026-05-25 02:34:31'),(38,'F001','Eksabor Ling 3.5\" /L',95000,105000,330,30,'2026-05-25 02:36:14'),(39,'F002','Lem Eksabor Raja',100000,125000,330,30,'2026-05-25 02:38:01'),(40,'F003','Paku Eksabor 2 cm/Dus',20000,40000,330,30,'2026-05-25 02:39:28'),(41,'G001','Kolset Jongkok KA',230000,300000,10,5,'2026-05-25 02:40:41'),(42,'G002','Kolset Jongkok AMS',250000,400000,330,30,'2026-05-25 02:41:53'),(43,'G003','Kaca Blok',35000,45000,30,20,'2026-05-25 02:43:03'),(44,'G004','Batu Angin Kupu-kupu',100000,70000,40,20,'2026-05-25 02:44:10'),(45,'G005','Batu Angin Super Minimalis',50000,75000,30,20,'2026-05-25 02:45:12'),(46,'H001','Piso Plamer',5000,7000,30,20,'2026-05-25 02:46:13'),(47,'H002','Kuku Kramik 20 cm',4000,5000,30,20,'2026-05-25 02:47:31'),(48,'H003','Kertas Plas /meter',10000,15000,20,10,'2026-05-25 02:49:04'),(49,'I001','Tripleks 3\"',60000,75000,30,20,'2026-05-25 02:50:23'),(50,'I002','Tripleks 5\"',100000,125000,30,20,'2026-05-25 02:51:33'),(51,'I003','Tripleks 6\"',110000,135000,30,20,'2026-05-25 02:52:28'),(52,'I004','Tripleks 9\"',130000,165000,30,20,'2026-05-25 02:54:09'),(53,'I005','Tripleks 12\"',200000,250000,30,20,'2026-05-25 02:55:04'),(54,'I006','Tripleks 15\"',235000,275000,30,20,'2026-05-25 02:56:07'),(55,'H004','Plamir Baglion',260000,280000,30,20,'2026-05-25 02:57:09'),(56,'J001','Krang Air',35000,60000,30,20,'2026-05-25 02:59:01'),(57,'C005','Paku Seng',25000,35000,330,30,'2026-05-25 02:59:46'),(58,'J002','Selang Tukang',15000,22000,30,20,'2026-05-28 06:54:43'),(59,'J003','Selang Air',7000,10000,30,20,'2026-05-28 06:56:27'),(60,'K001','Kabel 2x1,5 mm2',15000,20000,30,20,'2026-05-28 07:00:27'),(61,'K002','Kabel 2x2,5 mm',15000,25000,30,20,'2026-05-28 07:01:13'),(62,'L001','Lem Plamir (fox)',10000,20000,30,20,'2026-05-28 07:02:11'),(63,'L002','Lem Pipa',7000,10000,30,20,'2026-05-28 07:02:47'),(64,'F004','Les Plan Eksabor 20',55000,85000,30,20,'2026-05-28 07:05:50'),(65,'F005','Les Plan Eksabor 30',65000,95000,30,20,'2026-05-28 07:06:52'),(66,'M001','Seng Licin Lebar 1 meter',65000,95000,30,20,'2026-05-28 07:08:43'),(67,'A005','Besi Hollow Plapon 4x4',25000,40000,30,20,'2026-05-28 07:17:14'),(68,'A006','Besi Plapon Hollow 2x4',15000,30000,30,20,'2026-05-28 07:18:05'),(69,'N001','Karoro',15000,30000,30,20,'2026-05-28 07:18:41'),(70,'N002','Tali / meter',45000,65000,30,20,'2026-05-28 07:19:27'),(71,'N003','Semen Tonasa',81000,105000,-2,20,'2026-05-28 07:20:10'),(72,'P001','Kuas Roll Besar',35000,40000,30,20,'2026-05-28 07:21:01'),(73,'P002','Kuas Roll Kecil',20000,35000,30,20,'2026-05-28 07:21:32'),(74,'P003','Mesin Pompa Air (Sumitsu)',950000,1050000,30,20,'2026-05-28 07:22:26'),(75,'P004','Parlak',15000,25000,30,20,'2026-05-28 07:23:26'),(76,'Q001','Pipa Kotak Galvanix 2x4',100000,135000,30,20,'2026-05-28 07:24:49'),(77,'Q002','Pipa Kotak Galvanix 4x4',130000,170000,30,20,'2026-05-28 07:25:40'),(78,'Q003','Kanal (Vivo)',110000,130000,30,20,'2026-05-28 07:26:16'),(79,'R001','Ring',45000,65000,30,20,'2026-05-28 07:26:46'),(80,'R002','Westafel Cuci Piring American Standar 2 Lubang',550000,650000,20,10,'2026-05-28 07:27:43'),(81,'R003','Westafel Cuci Piring American Standar 1 Lubang',280000,400000,20,10,'2026-05-28 07:28:39'),(82,'S001','Terpal 2x4',50000,70000,20,10,'2026-05-28 07:29:27'),(83,'S002','Terpal 4x6',110000,145000,20,10,'2026-05-28 07:30:13'),(84,'S003','Terpal 6x8',200000,275000,20,10,'2026-05-28 07:30:45'),(85,'Q004','Pipa Kotak  Galvanix 4x6',130000,190000,30,20,'2026-05-28 07:31:35'),(86,'S004','Linggis Panjang 110cm',95000,135000,20,10,'2026-05-28 07:32:41'),(87,'S005','Linggis Panjang 70cm',70000,90000,20,10,'2026-05-28 07:33:33'),(88,'T001','Gipsun',80000,100000,30,20,'2026-05-28 07:34:10'),(89,'T002','Lem  Gipsun',100000,115000,30,20,'2026-05-28 07:34:49'),(90,'T003','Baut 1/2 Panjang x 20cm',12000,18000,30,20,'2026-05-28 07:35:39'),(91,'T004','Baut 3/4 Panjang x 20cm',10000,16000,30,20,'2026-05-28 07:36:24'),(92,'T005','Baut 3/4 Panjang x 5cm',8000,14000,30,20,'2026-05-28 07:37:12'),(93,'U001','Keramik 60x60',215000,250000,30,20,'2026-05-28 07:37:53'),(94,'U002','Keramik 50x50',76000,150000,30,20,'2026-05-28 07:38:21'),(95,'U003','Keramik 40x40',57000,130000,30,20,'2026-05-28 07:38:51'),(96,'U004','Keramik  Sanpawer 40x25',76000,150000,30,20,'2026-05-28 07:39:26'),(97,'V001','Pipa Air pawwer 4 inc',190000,210000,30,20,'2026-05-28 07:40:54'),(98,'V002','Pipa Air 3 inc',110000,140000,30,20,'2026-05-28 07:41:30'),(99,'V003','Pipa Air 2,1/2 inc',120000,130000,30,20,'2026-05-28 07:42:17'),(100,'V004','Pipa Air 2 inc',85000,100000,30,20,'2026-05-28 07:42:52'),(101,'V005','Pipa Air 1,1/2 inc',70000,90000,30,20,'2026-05-28 07:43:22'),(102,'V006','Pipa Air 1 inc',55000,65000,30,20,'2026-05-28 07:43:49'),(103,'V007','Pipa Air 3/4 inc',40000,55000,30,20,'2026-05-28 07:44:20'),(104,'V008','Pipa Air 1/2 inc',20000,40000,30,20,'2026-05-28 07:45:38');
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barang_masuk`
--

DROP TABLE IF EXISTS `barang_masuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang_masuk` (
  `id_masuk` int NOT NULL AUTO_INCREMENT,
  `id_barang` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `harga_beli` int DEFAULT NULL,
  `harga_jual` int NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_masuk`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang_masuk`
--

LOCK TABLES `barang_masuk` WRITE;
/*!40000 ALTER TABLE `barang_masuk` DISABLE KEYS */;
INSERT INTO `barang_masuk` VALUES (6,44,10,100000,70000,'baru masuk','2026-05-28 11:26:59');
/*!40000 ALTER TABLE `barang_masuk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `id_customer` int NOT NULL AUTO_INCREMENT,
  `nama_customer` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penjualan` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_penjualan` int NOT NULL,
  `id_barang` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga` int NOT NULL,
  `subtotal` int NOT NULL,
  PRIMARY KEY (`id_detail`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` VALUES (63,60,24,1,30000,0),(64,61,71,2,105000,0);
/*!40000 ALTER TABLE `detail_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manajemen_user`
--

DROP TABLE IF EXISTS `manajemen_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manajemen_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `level` enum('admin','kasir') COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `aksi` int NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan` (
  `id_penjualan` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `total_harga` int NOT NULL,
  `bayar` int NOT NULL,
  `metode_pembayaran` int NOT NULL,
  `referensi` int NOT NULL,
  `kasir` int NOT NULL,
  `id_customer` int NOT NULL,
  `kembali` int NOT NULL,
  `nama_customer` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `status_pembayaran` enum('Lunas','Belum Lunas') COLLATE utf8mb4_general_ci NOT NULL,
  `keuntungan` int NOT NULL,
  `id_user` int NOT NULL,
  PRIMARY KEY (`id_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (53,'2026-05-28',360000,400000,0,0,0,0,40000,'','0000-00-00','Lunas',100000,0),(54,'2026-05-28',195000,200000,0,0,0,0,5000,'','0000-00-00','Lunas',44000,0),(55,'2026-05-28',90000,100000,0,0,0,0,10000,'','0000-00-00','Lunas',25000,0),(56,'2026-05-28',210000,250000,0,0,0,0,40000,'','0000-00-00','Lunas',48000,0),(57,'2026-05-28',210000,210000,0,0,0,0,0,'','0000-00-00','Lunas',48000,0),(58,'2026-05-28',210000,210000,0,0,0,0,0,'','0000-00-00','Lunas',48000,0),(59,'2026-06-06',30000,50000,0,0,0,0,20000,'','0000-00-00','Lunas',10000,0),(60,'2026-06-06',30000,50000,0,0,0,0,20000,'','0000-00-00','Lunas',10000,0),(61,'2026-06-06',210000,220000,0,0,0,0,10000,'','0000-00-00','Lunas',48000,0);
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan_detail`
--

DROP TABLE IF EXISTS `penjualan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_barang` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `harga_jual` int DEFAULT NULL,
  `status_sync` enum('pending','sync') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `piutang` (
  `id_piutang` int NOT NULL AUTO_INCREMENT,
  `id_penjualan` int DEFAULT NULL,
  `id_customer` int DEFAULT NULL,
  `total_piutang` int DEFAULT NULL,
  `sisa_piutang` int DEFAULT NULL,
  `status` enum('Belum Lunas','Lunas') COLLATE utf8mb4_general_ci DEFAULT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profil_toko` (
  `id_toko` int NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_usaha` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  `telepon` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `logo` varchar(300) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_toko`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profil_toko`
--

LOCK TABLES `profil_toko` WRITE;
/*!40000 ALTER TABLE `profil_toko` DISABLE KEYS */;
INSERT INTO `profil_toko` VALUES (1,'MITRA AZAM','Toko Bangunan','Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual, Kabupaten Seram Bagian Barat',2147483647,'mitraazam@gmail.com','Sistem kasir modern toko bangunan','logo_1778768991_154.jpeg');
/*!40000 ALTER TABLE `profil_toko` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting` (
  `id_setting` int NOT NULL AUTO_INCREMENT,
  `tema` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'light',
  `notifikasi_stok` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `auto_backup` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'nonaktif',
  `terakhir_backup` datetime DEFAULT NULL,
  PRIMARY KEY (`id_setting`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES (1,'light','aktif','aktif','2026-05-15 10:34:56');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `level` enum('admin','kasir') COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `tema` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'light',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (18,'marlin','marlin','$2y$10$srMy3rWgYAd4dUafn/DJM.X4kp4cvnPrZcY2yWoSO4NcjRAGqmVTy','admin','admin','2026-05-15 09:46:42','light'),(19,'alin','alin','$2y$10$ZNy6YptbYTDfQqmWEA4ZKe.6J7UTWLL5sd7cuTafjI3R8QFF8RgY.','kasir','kasir','2026-05-15 05:29:11','light');
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

-- Dump completed on 2026-05-15 10:40:11
