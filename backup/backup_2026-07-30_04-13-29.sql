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
  `panjang_standar` decimal(10,2) DEFAULT NULL,
  `lebar_standar` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (20,'A001','Besi 12 standar',85000,125000,328,30,'2026-05-25','Normal',NULL,NULL),(21,'A002','Besi 10 standar',65000,90000,325,30,'2026-05-25','Normal',NULL,NULL),(22,'A003','Besi 8 standar',40000,70000,330,30,'2026-05-25','Normal',NULL,NULL),(23,'A004','Besi 6 standar',35000,40000,329,30,'2026-05-25','Normal',NULL,NULL),(24,'B001','Bendrat /kg',20000,30000,321,30,'2026-05-25','Normal',NULL,NULL),(25,'C001','Paku 4-5-7-10-12- cm/kg',20000,30000,330,30,'2026-05-25','Normal',NULL,NULL),(26,'C002','Paku 2-3 cm/kg',20000,40000,330,30,'2026-05-25','Normal',NULL,NULL),(27,'C003','Paku beton / Dus 5 - 7 - 10',20000,35000,330,30,'2026-05-25','Normal',NULL,NULL),(28,'C004','Paku putih 4 perkah 5 - 7 - 10 cm/kg',25000,40000,330,30,'2026-05-25','Normal',NULL,NULL),(29,'D001','Zen Putih Gajah Mas  atau Rusa 0,20/L',65000,75000,328,30,'2026-05-25','Normal',NULL,NULL),(30,'D002','Zen Spandek 3 m/L',95000,135000,330,30,'2026-05-25','Normal',NULL,NULL),(31,'D003','Zen Spandek 4 m/L',120000,169000,330,30,'2026-05-25','Normal',NULL,NULL),(32,'D004','Zen Resin Biru/L',65000,75000,330,30,'2026-05-25','Normal',NULL,NULL),(33,'D005','Zen Merah/L',65000,75000,330,30,'2026-05-25','Normal',NULL,NULL),(34,'D006','Zen Licin/Talang 30 cm/m',15000,20000,330,30,'2026-05-25','Normal',NULL,NULL),(35,'D007','Zen Licin/Talang 40 cm/m',18000,25000,330,30,'2026-05-25','Normal',NULL,NULL),(36,'D008','Zen Licin/Talang 50 cm/m',20000,30000,330,30,'2026-05-25','Normal',NULL,NULL),(37,'E001','Skop Cap Mata',120000,150000,326,30,'2026-05-25','Normal',NULL,NULL),(38,'F001','Eksabor Ling 3.5\" /L',95000,105000,327,30,'2026-05-25','Normal',NULL,NULL),(39,'F002','Lem Eksabor Raja',100000,125000,328,30,'2026-05-25','Normal',NULL,NULL),(40,'F003','Paku Eksabor 2 cm/Dus',20000,40000,330,30,'2026-05-25','Normal',NULL,NULL),(41,'G001','Kolset Jongkok KA',230000,300000,9,5,'2026-05-25','Normal',NULL,NULL),(42,'G002','Kolset Jongkok AMS',250000,400000,329,30,'2026-05-25','Normal',NULL,NULL),(43,'G003','Kaca Blok',35000,45000,27,20,'2026-05-25','Normal',NULL,NULL),(44,'G004','Batu Angin Kupu-kupu',55000,70000,33,20,'2026-05-25','Normal',NULL,NULL),(45,'G005','Batu Angin Super Minimalis',50000,75000,24,20,'2026-05-25','Normal',NULL,NULL),(46,'H001','Piso Plamer',5000,7000,30,20,'2026-05-25','Normal',NULL,NULL),(47,'H002','Kuku Kramik 20 cm',4000,5000,30,20,'2026-05-25','Normal',NULL,NULL),(48,'H003','Kertas Plas /meter',10000,15000,20,10,'2026-05-25','Normal',NULL,NULL),(49,'I001','Tripleks 3\"',60000,75000,30,20,'2026-05-25','Normal',NULL,NULL),(50,'I002','Tripleks 5\"',100000,125000,30,20,'2026-05-25','Normal',NULL,NULL),(51,'I003','Tripleks 6\"',110000,135000,30,20,'2026-05-25','Normal',NULL,NULL),(52,'I004','Tripleks 9\"',130000,165000,30,20,'2026-05-25','Normal',NULL,NULL),(53,'I005','Tripleks 12\"',200000,250000,29,20,'2026-05-25','Normal',NULL,NULL),(54,'I006','Tripleks 15\"',235000,275000,30,20,'2026-05-25','Normal',NULL,NULL),(55,'H004','Plamir Baglion',260000,280000,30,20,'2026-05-25','Normal',NULL,NULL),(56,'J001','Krang Air',35000,60000,30,20,'2026-05-25','Normal',NULL,NULL),(57,'C005','Paku Seng',25000,35000,330,30,'2026-05-25','Normal',NULL,NULL),(58,'J002','Selang Tukang',15000,22000,30,20,'2026-05-28','Normal',NULL,NULL),(59,'J003','Selang Air',7000,10000,30,20,'2026-05-28','Normal',NULL,NULL),(60,'K001','Kabel 2x1,5 mm2',15000,20000,27,20,'2026-05-28','Normal',NULL,NULL),(61,'K002','Kabel 2x2,5 mm',15000,25000,29,20,'2026-05-28','Normal',NULL,NULL),(62,'L001','Lem Plamir (fox)',10000,20000,30,20,'2026-05-28','Normal',NULL,NULL),(63,'L002','Lem Pipa',7000,10000,30,20,'2026-05-28','Normal',NULL,NULL),(64,'F004','Les Plan Eksabor 20',55000,85000,30,20,'2026-05-28','Normal',NULL,NULL),(65,'F005','Les Plan Eksabor 30',65000,95000,30,20,'2026-05-28','Normal',NULL,NULL),(66,'M001','Seng Licin Lebar 1 meter',65000,95000,29,20,'2026-05-28','Normal',NULL,NULL),(67,'A005','Besi Hollow Plapon 4x4',25000,40000,27,20,'2026-05-28','Normal',NULL,NULL),(68,'A006','Besi Plapon Hollow 2x4',15000,30000,30,20,'2026-05-28','Normal',NULL,NULL),(69,'N001','Karoro',15000,30000,30,20,'2026-05-28','Normal',NULL,NULL),(70,'N002','Tali / meter',45000,65000,29,20,'2026-05-28','Normal',NULL,NULL),(71,'N003','Semen Tonasa',81000,105000,4,20,'2026-05-28','Normal',NULL,NULL),(72,'P001','Kuas Roll Besar',35000,40000,29,20,'2026-05-28','Normal',NULL,NULL),(73,'P002','Kuas Roll Kecil',20000,35000,30,20,'2026-05-28','Normal',NULL,NULL),(74,'P003','Mesin Pompa Air (Sumitsu)',950000,1050000,30,20,'2026-05-28','Normal',NULL,NULL),(75,'P004','Parlak',15000,25000,30,20,'2026-05-28','Normal',NULL,NULL),(76,'Q001','Pipa Kotak Galvanix 2x4',100000,135000,30,20,'2026-05-28','Normal',NULL,NULL),(77,'Q002','Pipa Kotak Galvanix 4x4',130000,170000,30,20,'2026-05-28','Normal',NULL,NULL),(78,'Q003','Kanal (Vivo)',110000,130000,26,20,'2026-05-28','Normal',NULL,NULL),(79,'R001','Ring',45000,65000,30,20,'2026-05-28','Normal',NULL,NULL),(80,'R002','Westafel Cuci Piring American Standar 2 Lubang',550000,650000,19,10,'2026-05-28','Normal',NULL,NULL),(81,'R003','Westafel Cuci Piring American Standar 1 Lubang',280000,400000,18,10,'2026-05-28','Normal',NULL,NULL),(82,'S001','Terpal 2x4',50000,70000,20,10,'2026-05-28','Normal',NULL,NULL),(83,'S002','Terpal 4x6',110000,145000,20,10,'2026-05-28','Normal',NULL,NULL),(84,'S003','Terpal 6x8',200000,275000,20,10,'2026-05-28','Normal',NULL,NULL),(85,'Q004','Pipa Kotak  Galvanix 4x6',130000,190000,29,20,'2026-05-28','Normal',NULL,NULL),(86,'S004','Linggis Panjang 110cm',95000,135000,20,10,'2026-05-28','Normal',NULL,NULL),(87,'S005','Linggis Panjang 70cm',70000,90000,20,10,'2026-05-28','Normal',NULL,NULL),(88,'T001','Gipsun',80000,100000,30,20,'2026-05-28','Normal',NULL,NULL),(89,'T002','Lem  Gipsun',100000,115000,30,20,'2026-05-28','Normal',NULL,NULL),(90,'T003','Baut 1/2 Panjang x 20cm',12000,18000,29,20,'2026-05-28','Normal',NULL,NULL),(91,'T004','Baut 3/4 Panjang x 20cm',10000,16000,29,20,'2026-05-28','Normal',NULL,NULL),(92,'T005','Baut 3/4 Panjang x 5cm',8000,14000,29,20,'2026-05-28','Normal',NULL,NULL),(93,'U001','Keramik 60x60',215000,250000,29,20,'2026-05-28','Normal',NULL,NULL),(94,'U002','Keramik 50x50',76000,150000,30,20,'2026-05-28','Normal',NULL,NULL),(95,'U003','Keramik 40x40',57000,130000,30,20,'2026-05-28','Normal',NULL,NULL),(96,'U004','Keramik  Sanpawer 40x25',76000,150000,29,20,'2026-05-28','Normal',NULL,NULL),(97,'V001','Pipa Air pawwer 4 inc',190000,210000,30,20,'2026-05-28','Normal',NULL,NULL),(98,'V002','Pipa Air 3 inc',110000,140000,30,20,'2026-05-28','Normal',NULL,NULL),(99,'V003','Pipa Air 2,1/2 inc',120000,130000,30,20,'2026-05-28','Normal',NULL,NULL),(100,'V004','Pipa Air 2 inc',85000,100000,30,20,'2026-05-28','Normal',NULL,NULL),(101,'V005','Pipa Air 1,1/2 inc',70000,90000,30,20,'2026-05-28','Normal',NULL,NULL),(102,'V006','Pipa Air 1 inc',55000,65000,30,20,'2026-05-28','Normal',NULL,NULL),(103,'V007','Pipa Air 3/4 inc',40000,55000,30,20,'2026-05-28','Normal',NULL,NULL),(104,'V008','Pipa Air 1/2 inc',20000,40000,30,20,'2026-05-28','Normal',NULL,NULL),(113,'KSHUE','Kaca',400000,450000,-1,2,'2026-07-17','Kaca',200.00,200.00),(114,'KBHT12','Kaca',400000,450000,8,3,'2026-07-20','',NULL,NULL);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
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
  `harga_satuan_beli` decimal(15,2) DEFAULT 0.00,
  `subtotal` int(11) NOT NULL,
  `keuntungan_item` decimal(15,2) DEFAULT 0.00,
  `panjang` float NOT NULL DEFAULT 0,
  `lebar` float NOT NULL DEFAULT 0,
  `kebutuhan` decimal(10,2) DEFAULT 1.00,
  PRIMARY KEY (`id_detail`)
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` VALUES (131,124,67,1,40000,25000.00,40000,15000.00,0,0,1.00),(132,124,24,1,30000,20000.00,30000,10000.00,0,0,1.00),(133,124,85,1,190000,130000.00,190000,60000.00,0,0,1.00),(134,125,37,1,150000,120000.00,150000,30000.00,0,0,1.00),(135,125,72,1,40000,35000.00,40000,5000.00,0,0,1.00),(136,125,39,1,125000,100000.00,125000,25000.00,0,0,1.00),(137,125,29,1,75000,65000.00,75000,10000.00,0,0,1.00),(138,126,60,1,20000,15000.00,20000,5000.00,0,0,1.00),(139,126,78,1,130000,110000.00,130000,20000.00,0,0,1.00),(140,126,24,1,30000,20000.00,30000,10000.00,0,0,1.00),(141,127,45,1,75000,50000.00,75000,25000.00,0,0,1.00),(142,127,44,1,70000,55000.00,70000,15000.00,0,0,1.00),(143,128,37,1,150000,120000.00,150000,30000.00,0,0,1.00),(144,128,81,1,400000,280000.00,400000,120000.00,0,0,1.00),(145,128,66,1,95000,65000.00,95000,30000.00,0,0,1.00),(146,129,80,1,650000,550000.00,650000,100000.00,0,0,1.00),(147,130,45,1,75000,50000.00,75000,25000.00,0,0,1.00),(148,131,45,1,75000,50000.00,75000,25000.00,0,0,1.00),(149,132,24,1,30000,20000.00,30000,10000.00,0,0,1.00),(150,132,45,1,75000,50000.00,75000,25000.00,0,0,1.00),(151,133,44,1,70000,55000.00,70000,15000.00,0,0,1.00),(152,133,41,1,300000,230000.00,300000,70000.00,0,0,1.00),(153,133,45,1,75000,50000.00,75000,25000.00,0,0,1.00),(154,134,113,1,450000,400000.00,28125,3125.00,50,50,1.00),(155,135,113,1,450000,400000.00,28125,3125.00,50,50,1.00),(156,136,78,1,130000,110000.00,130000,20000.00,0,0,1.00),(157,137,39,1,125000,100000.00,125000,25000.00,0,0,1.00);
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
-- Table structure for table `pengeluaran`
--

DROP TABLE IF EXISTS `pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pengeluaran`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengeluaran`
--

LOCK TABLES `pengeluaran` WRITE;
/*!40000 ALTER TABLE `pengeluaran` DISABLE KEYS */;
INSERT INTO `pengeluaran` VALUES (5,'2026-07-22','Listrik & Air',100000.00,'',NULL);
/*!40000 ALTER TABLE `pengeluaran` ENABLE KEYS */;
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
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `referensi` varchar(100) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `kasir` int(11) DEFAULT NULL,
  `nama_kasir` varchar(100) DEFAULT NULL,
  `id_customer` int(11) DEFAULT NULL,
  `kembali` int(11) NOT NULL,
  `nama_customer` varchar(100) DEFAULT NULL,
  `jatuh_tempo` date DEFAULT NULL,
  `status_pembayaran` enum('Lunas','Belum Lunas') NOT NULL,
  `metode_pelunasan` varchar(30) DEFAULT NULL,
  `tanggal_pelunasan` datetime DEFAULT NULL,
  `bukti_pelunasan` varchar(255) DEFAULT NULL,
  `keuntungan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (124,'2026-07-22 21:39:01',260000,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-07-29','Lunas',NULL,'2026-07-22 21:40:34',NULL,85000,23),(125,'2026-07-22 22:21:36',390000,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-07-29','Lunas',NULL,'2026-07-22 22:23:48',NULL,70000,23),(126,'2026-07-22 22:33:20',180000,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-07-29','Lunas',NULL,'2026-07-22 22:34:38',NULL,35000,23),(127,'2026-07-22 23:32:15',145000,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-07-29','Belum Lunas',NULL,NULL,NULL,40000,23),(128,'2026-07-22 23:35:50',645000,645000,'QRIS','','bukti_20260722233550_2495.jpeg',NULL,NULL,NULL,0,'',NULL,'Lunas',NULL,NULL,NULL,180000,23),(129,'2026-07-22 23:37:06',650000,650000,'Transfer','BRI','bukti_20260722233706_1545.jpeg',NULL,NULL,NULL,0,'',NULL,'Lunas',NULL,NULL,NULL,100000,23),(130,'2026-07-22 23:38:23',75000,100000,'Tunai','','',NULL,NULL,NULL,25000,'',NULL,'Lunas',NULL,NULL,NULL,25000,23),(131,'2026-07-23 10:50:03',75000,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-07-30','Belum Lunas',NULL,NULL,NULL,25000,23),(132,'2026-07-23 10:54:26',105000,105000,'Tunai','','',NULL,NULL,NULL,0,'',NULL,'Lunas',NULL,NULL,NULL,35000,23),(133,'2026-07-23 13:44:18',445000,500000,'Tunai','','',NULL,NULL,NULL,55000,'',NULL,'Lunas',NULL,NULL,NULL,110000,23),(134,'2026-07-23 14:08:21',28125,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-07-30','Belum Lunas',NULL,NULL,NULL,3125,23),(135,'2026-07-23 14:09:06',28125,30000,'Tunai','','',NULL,NULL,NULL,1875,'',NULL,'Lunas',NULL,NULL,NULL,3125,23),(136,'2026-07-27 09:50:35',130000,0,'Hutang','','',NULL,NULL,NULL,0,'marlin','2026-08-03','Belum Lunas',NULL,NULL,NULL,20000,23),(137,'2026-07-27 09:55:30',125000,0,'Hutang','','',NULL,NULL,NULL,0,'serli','2026-08-05','Belum Lunas',NULL,NULL,NULL,25000,23);
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
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
  `telepon` int(11) NOT NULL,
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
INSERT INTO `profil_toko` VALUES (1,'MITRA AZAM','Toko Bangunan','Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual, Kabupaten Seram Bagian Barat',2147483647,'mitraazam@gmail.com','Sistem kasir modern toko bangunan','logo_1784628477_211.jpeg');
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
INSERT INTO `setting` VALUES (1,'light','aktif','aktif','2026-07-28 11:30:16');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_barang_masuk`
--

DROP TABLE IF EXISTS `stok_barang_masuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_barang_masuk` (
  `id_masuk` int(11) NOT NULL AUTO_INCREMENT,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga_beli` int(11) DEFAULT NULL,
  `harga_jual` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_masuk`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_barang_masuk`
--

LOCK TABLES `stok_barang_masuk` WRITE;
/*!40000 ALTER TABLE `stok_barang_masuk` DISABLE KEYS */;
INSERT INTO `stok_barang_masuk` VALUES (6,44,10,100000,70000,'baru masuk','2026-05-28 11:26:59'),(8,107,3,350000,400000,'pt.abadi','2026-06-24 15:08:53'),(9,107,2,400000,450000,'','2026-07-07 03:51:53'),(10,71,7,81000,105000,'pt.abadi','2026-07-27 03:31:29');
/*!40000 ALTER TABLE `stok_barang_masuk` ENABLE KEYS */;
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
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (22,'saul','admin','','','admin_1784263578_174.jpg','$2y$10$/RWWYRyNdl0gH5oNDE6k4eqLAMe8tJrWj/eMbbsqBwkJshZ8nZWhW','admin','','2026-07-30 11:08:33'),(23,'saul jannah','kasir','','','avatar_1784623813_873.jpg','$2y$10$sGgBDzT7kNTI.kugCQ8ZP.lFkFOyiMpPKVoqg7T7lW34.YGyozC9.','kasir','aktif','2026-07-27 09:50:13');
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

-- Dump completed on 2026-07-30 11:13:30
