package com.gmbtech.etraceability.data.api.models

import com.google.gson.annotations.SerializedName

// ─── Request bodies ────────────────────────────────────────────────────────
data class LoginRequest(
    val email: String,
    val password: String,
)

// ─── Responses ─────────────────────────────────────────────────────────────
data class LoginResponse(
    val token: String,
    @SerializedName("petani_id") val petaniId: Int,
    val nama: String,
    @SerializedName("kode_petani") val kodePetani: String,
)

data class ApiResponse<T>(
    val data: T?,
    val message: String?,
)

data class Profil(
    val id: Int,
    @SerializedName("kode_petani") val kodePetani: String,
    val nama: String,
    @SerializedName("no_hp") val noHp: String?,
    val desa: String?,
    val kecamatan: String?,
    val kabupaten: String?,
    val aktif: Boolean,
)

data class Lahan(
    val id: Int,
    @SerializedName("kode_lahan") val kodeLahan: String,
    @SerializedName("nama_lahan") val namaLahan: String?,
    @SerializedName("pohon_di_deres") val pohonDiDeres: Int,
    @SerializedName("kelapa_buah") val kelapaBuah: Int?,
    val luas: Double?,
    val desa: String?,
)

data class Setoran(
    val id: Int,
    @SerializedName("tanggal_setor") val tanggalSetor: String,
    @SerializedName("jenis_produk") val jenisProduk: String,
    @SerializedName("berat_kg") val beratKg: Double,
    @SerializedName("total_harga") val totalHarga: Double,
    @SerializedName("hari_akumulasi") val hariAkumulasi: Int?,
    @SerializedName("is_anomali") val isAnoali: Boolean,
    @SerializedName("keterangan_anomali") val keteranganAnoali: String?,
)

data class SetoranPaginated(
    val data: List<Setoran>,
    @SerializedName("current_page") val currentPage: Int,
    @SerializedName("last_page") val lastPage: Int,
    val total: Int,
)

data class RekapBulan(
    val bulan: String,
    val jumlah: Int,
    @SerializedName("total_kg") val totalKg: Double,
    @SerializedName("total_harga") val totalHarga: Double,
    val anomali: Int,
)

data class RekapTotal(
    @SerializedName("total_kg") val totalKg: Double,
    @SerializedName("total_harga") val totalHarga: Double,
    @SerializedName("jumlah_setor") val jumlahSetor: Int,
    @SerializedName("jumlah_anomali") val jumlahAnoali: Int,
)

data class RekapResponse(
    val bulanan: List<RekapBulan>,
    val total: RekapTotal,
)
