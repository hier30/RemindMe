// Menghitung prioritas berdasarkan deadline (hari tersisa)
function hitungPrioritas(deadlineDate) {
  const today = new Date();
  const deadline = new Date(deadlineDate);
  const hariTersisa = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24));

  if (hariTersisa < 0) return "kedaluwarsa";
  if (hariTersisa <= 2) return "tinggi";
  if (hariTersisa <= 7) return "sedang";
  return "rendah";
}

// Status tugas
function statusTugas(selesai) {
  return selesai ? "selesai" : "belum selesai";
}

module.exports = { hitungPrioritas, statusTugas };