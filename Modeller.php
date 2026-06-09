<?php

abstract class EczaneModeli {
    protected $vt;
    protected $tablo_adi;

    public function __construct($vt) {
        $this->vt = $vt;
    }

    public function tumunu_getir() {
        $sorgu_sql = "SELECT * FROM {$this->tablo_adi} ORDER BY id DESC";
        $sonuc = $this->vt->query($sorgu_sql);
        $veri = [];
        if ($sonuc) {
            while ($satir = $sonuc->fetch_assoc()) {
                $veri[] = $satir;
            }
        }
        return $veri;
    }

    public function id_ile_getir($id) {
        $ifade = $this->vt->prepare("SELECT * FROM {$this->tablo_adi} WHERE id = ?");
        $ifade->bind_param("i", $id);
        $ifade->execute();
        $sonuc = $ifade->get_result();
        return $sonuc->fetch_assoc();
    }

    public function sil_islemi($id) {
        try {
            $ifade = $this->vt->prepare("DELETE FROM {$this->tablo_adi} WHERE id = ?");
            $ifade->bind_param("i", $id);
            return $ifade->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
    
    abstract public function olustur($veri);
    abstract public function guncelleme($id, $veri);
}

class Kategori extends EczaneModeli {
    protected $tablo_adi = 'kategoriler';

    public function olustur($veri) {
        $ifade = $this->vt->prepare("INSERT INTO kategoriler (ad) VALUES (?)");
        $ifade->bind_param("s", $veri['ad']);
        return $ifade->execute();
    }

    public function guncelleme($id, $veri) {
        $ifade = $this->vt->prepare("UPDATE kategoriler SET ad = ? WHERE id = ?");
        $ifade->bind_param("si", $veri['ad'], $id);
        return $ifade->execute();
    }
}

class Ilac extends EczaneModeli {
    protected $tablo_adi = 'ilaclar';

    public function tumunu_getir() {
        $sorgu_sql = "SELECT i.*, k.ad as kategori_adi FROM ilaclar i LEFT JOIN kategoriler k ON i.kategori_id = k.id ORDER BY i.id DESC";
        $sonuc = $this->vt->query($sorgu_sql);
        $veri = [];
        if ($sonuc) {
            while ($satir = $sonuc->fetch_assoc()) {
                $veri[] = $satir;
            }
        }
        return $veri;
    }

    public function olustur($veri) {
        try {
            $ifade = $this->vt->prepare("INSERT INTO ilaclar (ad, kategori_id, stok, fiyat, aciklama) VALUES (?, ?, ?, ?, ?)");
            $kategori_id = !empty($veri['kategori_id']) ? intval($veri['kategori_id']) : null;
            $ifade->bind_param("siids", $veri['ad'], $kategori_id, $veri['stok'], $veri['fiyat'], $veri['aciklama']);
            return $ifade->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function guncelleme($id, $veri) {
        try {
            $ifade = $this->vt->prepare("UPDATE ilaclar SET ad = ?, kategori_id = ?, stok = ?, fiyat = ?, aciklama = ? WHERE id = ?");
            $kategori_id = !empty($veri['kategori_id']) ? intval($veri['kategori_id']) : null;
            $ifade->bind_param("siidsi", $veri['ad'], $kategori_id, $veri['stok'], $veri['fiyat'], $veri['aciklama'], $id);
            return $ifade->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}

class Hasta extends EczaneModeli {
    protected $tablo_adi = 'hastalar';

    public function olustur($veri) {
        try {
            $ifade = $this->vt->prepare("INSERT INTO hastalar (tc, ad, soyad, telefon, adres) VALUES (?, ?, ?, ?, ?)");
            $ifade->bind_param("sssss", $veri['tc'], $veri['ad'], $veri['soyad'], $veri['telefon'], $veri['adres']);
            return $ifade->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function guncelleme($id, $veri) {
        try {
            $ifade = $this->vt->prepare("UPDATE hastalar SET tc = ?, ad = ?, soyad = ?, telefon = ?, adres = ? WHERE id = ?");
            $ifade->bind_param("sssssi", $veri['tc'], $veri['ad'], $veri['soyad'], $veri['telefon'], $veri['adres'], $id);
            return $ifade->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}

class Satis extends EczaneModeli {
    protected $tablo_adi = 'satislar';

    public function detayli_tumunu_getir() {
        $sorgu = "SELECT s.id, s.tarih as satis_tarihi, s.toplam_tutar as toplam_fiyat, 
                  SUM(sd.adet) as adet, GROUP_CONCAT(CONCAT(i.ad, ' (', sd.adet, ' Adet)') SEPARATOR ', ') as ilac_ad, 
                  CONCAT(h.ad, ' ', h.soyad) as hasta_ad 
                  FROM satislar s 
                  JOIN satis_detay sd ON s.id = sd.satis_id 
                  JOIN ilaclar i ON sd.ilac_id = i.id 
                  JOIN hastalar h ON s.hasta_id = h.id 
                  GROUP BY s.id 
                  ORDER BY s.id DESC";
        $sonuc = $this->vt->query($sorgu);
        $veri = [];
        if ($sonuc) {
            while ($satir = $sonuc->fetch_assoc()) {
                $veri[] = $satir;
            }
        }
        return $veri;
    }

    public function olustur($veri) {
        $secilen_hasta_id = intval($veri['hasta_id']); 
        $kullanici_id = !empty($veri['kullanici_id']) ? intval($veri['kullanici_id']) : null;
        $ilaclar_listesi = $veri['ilaclar'];

        $toplam_tutar = 0;
        $gecerli_ilaclar = [];

        foreach ($ilaclar_listesi as $secim) {
            $ilac_id = intval($secim['ilac_id']);
            $miktar = intval($secim['adet']);

            $ifade = $this->vt->prepare("SELECT fiyat, stok, ad FROM ilaclar WHERE id = ?"); 
            $ifade->bind_param("i", $ilac_id);
            $ifade->execute();
            $sonuc = $ifade->get_result();
            $ilac = $sonuc->fetch_assoc();
            
            if (!$ilac) { 
                return ['durum' => false, 'mesaj' => 'İlaç bulunamadı.'];
            }
            if ($ilac['stok'] < $miktar) { 
                return ['durum' => false, 'mesaj' => $ilac['ad'] . ' için yetersiz stok!'];
            }
            
            $birim_fiyat = $ilac['fiyat'];
            $toplam_tutar += $miktar * $birim_fiyat;
            
            $gecerli_ilaclar[] = [
                'ilac_id' => $ilac_id,
                'adet' => $miktar,
                'birim_fiyat' => $birim_fiyat
            ];
        }
        
        try {
            $this->vt->begin_transaction();
            
            $stmtSale = $this->vt->prepare("INSERT INTO satislar (tarih, kullanici_id, hasta_id, toplam_tutar) VALUES (NOW(), ?, ?, ?)");
            $stmtSale->bind_param("iid", $kullanici_id, $secilen_hasta_id, $toplam_tutar);
            $stmtSale->execute();
            $satis_id = $this->vt->insert_id;
            
            foreach ($gecerli_ilaclar as $ilac) {
                $stmtDetail = $this->vt->prepare("INSERT INTO satis_detay (satis_id, ilac_id, adet, birim_fiyat) VALUES (?, ?, ?, ?)");
                $stmtDetail->bind_param("iiid", $satis_id, $ilac['ilac_id'], $ilac['adet'], $ilac['birim_fiyat']);
                $stmtDetail->execute();
                
                $stmtUpdate = $this->vt->prepare("UPDATE ilaclar SET stok = stok - ? WHERE id = ?");
                $stmtUpdate->bind_param("ii", $ilac['adet'], $ilac['ilac_id']);
                $stmtUpdate->execute();
            }
            
            $this->vt->commit();
            return ['durum' => true];
        } catch (Exception $e) {
            $this->vt->rollback();
            throw $e;
        }
    }

    public function guncelleme($id, $veri) {
        return false;
    }
}

class Kullanici extends EczaneModeli {
    protected $tablo_adi = 'kullanicilar';

    public function olustur($veri) {
        try {
            $ifade = $this->vt->prepare("INSERT INTO kullanicilar (isim, soyisim, kullanici_adi, sifre, rol) VALUES (?, ?, ?, ?, ?)");
            $rol = !empty($veri['rol']) ? ucfirst(strtolower($veri['rol'])) : 'Personel';
            if ($rol == 'Mudur' || $rol == 'mudur' || $rol == 'müdür') $rol = 'Müdür';
            $ifade->bind_param("sssss", $veri['isim'], $veri['soyisim'], $veri['kullanici_adi'], $veri['sifre'], $rol);
            return $ifade->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function guncelleme($id, $veri) {
        $ifade = $this->vt->prepare("SELECT id FROM kullanicilar WHERE id = ? AND sifre = ?");
        $ifade->bind_param("is", $id, $veri['mevcut_sifre']);
        $ifade->execute();
        $sonuc = $ifade->get_result();
        
        if ($sonuc->fetch_assoc()) {
            $guncelleme = $this->vt->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
            $guncelleme->bind_param("si", $veri['yeni_sifre'], $id);
            return $guncelleme->execute();
        }
        return false;
    }

    public function giris_yap($kullanici_adi, $sifre) {
        $ifade = $this->vt->prepare("SELECT id, isim, soyisim, kullanici_adi, rol FROM kullanicilar WHERE kullanici_adi = ? AND sifre = ?");
        $ifade->bind_param("ss", $kullanici_adi, $sifre);
        $ifade->execute();
        $sonuc = $ifade->get_result();
        return $sonuc->fetch_assoc();
    }
}

class Rapor {
    private $vt;

    public function __construct($vt) {
        $this->vt = $vt;
    }

    public function ozet_getir($baslangic, $bitis) {
        $sql = "SELECT COUNT(DISTINCT s.id) as toplam_satis, COALESCE(SUM(s.toplam_tutar), 0) as ciro, COALESCE(SUM(sd.adet), 0) as toplam_urun 
                FROM satislar s 
                LEFT JOIN satis_detay sd ON s.id = sd.satis_id 
                WHERE s.tarih >= ? AND s.tarih <= ?";
        $stmt = $this->vt->prepare($sql);
        $bitis .= ' 23:59:59';
        $baslangic .= ' 00:00:00';
        $stmt->bind_param("ss", $baslangic, $bitis);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tum_ozetleri_getir() {
        $bugun = date('Y-m-d');
        $hafta_baslangic = date('Y-m-d', strtotime('-6 days'));
        $ay_baslangic = date('Y-m-01');
        
        $gunluk = $this->ozet_getir($bugun, $bugun);
        $haftalik = $this->ozet_getir($hafta_baslangic, $bugun);
        $aylik = $this->ozet_getir($ay_baslangic, date('Y-m-t'));

        $genel_sql = "SELECT COUNT(DISTINCT s.id) as toplam_satis, COALESCE(SUM(s.toplam_tutar), 0) as ciro, COALESCE(SUM(sd.adet), 0) as toplam_urun FROM satislar s LEFT JOIN satis_detay sd ON s.id = sd.satis_id";
        $genel = $this->vt->query($genel_sql)->fetch_assoc();

        return [
            'gunluk' => $gunluk,
            'haftalik' => $haftalik,
            'aylik' => $aylik,
            'genel' => $genel
        ];
    }

    public function gunluk_ciro_grafigi() {
        $veri = ['tarihler' => [], 'cirolar' => []];
        for ($i = 6; $i >= 0; $i--) {
            $tarih = date('Y-m-d', strtotime("-$i days"));
            $gun_adi = date('d.m', strtotime($tarih));
            $ozet = $this->ozet_getir($tarih, $tarih);
            $veri['tarihler'][] = $gun_adi;
            $veri['cirolar'][] = (float)$ozet['ciro'];
        }
        return $veri;
    }

    public function en_cok_satilan_ilaclar($limit = 5) {
        $sql = "SELECT i.ad, SUM(sd.adet) as toplam_adet, SUM(sd.adet * sd.birim_fiyat) as toplam_gelir
                FROM satis_detay sd
                JOIN ilaclar i ON sd.ilac_id = i.id
                GROUP BY sd.ilac_id
                ORDER BY toplam_adet DESC
                LIMIT ?";
        $stmt = $this->vt->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $sonuc = $stmt->get_result();
        $veri = [];
        while ($satir = $sonuc->fetch_assoc()) {
            $veri[] = $satir;
        }
        return $veri;
    }

    public function tarih_aralikli_satislar($baslangic, $bitis) {
        $sql = "SELECT s.id, s.tarih as satis_tarihi, s.toplam_tutar as toplam_fiyat, 
                SUM(sd.adet) as adet, GROUP_CONCAT(CONCAT(i.ad, ' (', sd.adet, ' Adet)') SEPARATOR ', ') as ilac_ad, 
                CONCAT(h.ad, ' ', h.soyad) as hasta_ad 
                FROM satislar s 
                JOIN satis_detay sd ON s.id = sd.satis_id 
                JOIN ilaclar i ON sd.ilac_id = i.id 
                JOIN hastalar h ON s.hasta_id = h.id 
                WHERE s.tarih >= ? AND s.tarih <= ?
                GROUP BY s.id
                ORDER BY s.id DESC";
        $baslangic .= ' 00:00:00';
        $bitis .= ' 23:59:59';
        $stmt = $this->vt->prepare($sql);
        $stmt->bind_param("ss", $baslangic, $bitis);
        $stmt->execute();
        $sonuc = $stmt->get_result();
        $veri = [];
        while ($satir = $sonuc->fetch_assoc()) {
            $veri[] = $satir;
        }
        return $veri;
    }
}
?>
