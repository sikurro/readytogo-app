# Panduan Teknis Penilaian Kuis Ready To GO (R2G)

Dokumen ini ditujukan sebagai petunjuk teknis bagi petugas terkait mekanisme dan algoritma penilaian pada Modul Kuis K3 di aplikasi Ready To GO (R2G).

## 1. Konsep Penilaian (Scoring System)

Sistem penilaian kuis di R2G **tidak hanya menghitung jumlah jawaban yang benar**, melainkan menggunakan kombinasi antara **Akurasi (Ketepatan)** dan **Kecepatan Respons (Ketangkasan)**. 

Sistem ini dirancang sedemikian rupa untuk mensimulasikan kondisi lapangan di mana seorang petugas K3 dituntut untuk dapat mengambil keputusan yang tepat dalam waktu yang sangat singkat.

## 2. Algoritma Perhitungan Skor

Setiap kali petugas menjawab **benar** pada suatu soal, skor yang didapatkan akan dihitung menggunakan rumus berikut:

**Skor Soal = Poin Dasar + Bonus Kecepatan (Speed Bonus)**

Secara matematis:
> **$\text{Skor Tambahan} = 100 + (\text{Sisa Waktu dalam Detik} \times 10)$**

### Rincian Poin:
1. **100 Poin Dasar (Base Score):** Diberikan secara pasti untuk setiap soal yang dijawab dengan **benar**.
2. **10 Poin Bonus per Detik:** Diberikan untuk setiap detik waktu yang tersisa pada *timer* di soal tersebut saat tombol jawaban diklik. 
   - *Semakin cepat Anda menjawab, semakin besar bonus poin yang Anda kumpulkan.*
3. **0 Poin (Salah/Waktu Habis):** Jika jawaban yang dipilih salah, atau waktu (timer) habis sebelum memilih jawaban, petugas tidak akan mendapatkan poin (0) untuk soal tersebut.

---

## 3. Studi Kasus (Mengapa Jumlah Benar Sama/Lebih Banyak Tapi Skor Lebih Rendah?)

Seringkali muncul pertanyaan di Leaderboard: *"Mengapa Petugas A yang menjawab 7 soal benar memiliki peringkat/skor yang lebih tinggi dibandingkan Petugas B yang menjawab 8 soal benar?"*

Berikut adalah analisis simulasinya:

### Kasus Petugas B (Akurasi Tinggi, Kecepatan Rendah)
- **Kondisi:** Menjawab 8 soal dengan benar. Namun ia selalu menghabiskan banyak waktu untuk berpikir, sehingga sisa waktunya selalu sedikit (misal rata-rata sisa 5 detik).
- **Perhitungan Skor Rata-rata per Soal:** $100 + (5 \times 10) = 150 \text{ poin}$.
- **Total Skor Akhir:** $8 \text{ soal} \times 150 \text{ poin} = \mathbf{1200 \text{ poin}}$.

### Kasus Petugas A (Akurasi Cukup, Kecepatan Sangat Tinggi)
- **Kondisi:** Menjawab 7 soal dengan benar (1 salah). Namun pada soal yang ia jawab benar, ia merespons dengan **sangat cepat** (misal rata-rata sisa 13 detik).
- **Perhitungan Skor Rata-rata per Soal:** $100 + (13 \times 10) = 230 \text{ poin}$.
- **Total Skor Akhir:** $7 \text{ soal} \times 230 \text{ poin} = \mathbf{1610 \text{ poin}}$.

### Kesimpulan Kasus:
Petugas A ($1610 \text{ poin}$) berhasil mengungguli Petugas B ($1200 \text{ poin}$) di Leaderboard karena **kecepatannya menutupi kekurangan 1 soalnya**. 

Oleh karena itu, kunci untuk memuncaki Leaderboard R2G adalah:
**"Bacalah soal dengan cermat, lalu ambil keputusan (klik jawaban) secepat mungkin!"**
