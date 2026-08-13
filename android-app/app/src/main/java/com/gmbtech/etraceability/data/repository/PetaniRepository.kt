package com.gmbtech.etraceability.data.repository

import com.gmbtech.etraceability.data.api.ApiClient
import com.gmbtech.etraceability.data.api.models.*
import com.gmbtech.etraceability.data.session.SessionManager

sealed class Result<out T> {
    data class Success<T>(val data: T) : Result<T>()
    data class Error(val message: String) : Result<Nothing>()
    object Loading : Result<Nothing>()
}

class PetaniRepository(private val session: SessionManager) {

    private val api get() = ApiClient.service

    suspend fun login(email: String, password: String): Result<LoginResponse> {
        return try {
            val response = api.login(LoginRequest(email, password))
            if (response.isSuccessful) {
                val body = response.body()!!
                session.saveSession(body.token, body.nama, body.kodePetani, body.petaniId)
                Result.Success(body)
            } else {
                Result.Error(parseError(response.code()))
            }
        } catch (e: Exception) {
            Result.Error("Tidak dapat terhubung ke server: ${e.message}")
        }
    }

    suspend fun logout() {
        session.clearSession()
    }

    suspend fun getProfil(): Result<Profil> {
        return try {
            val response = api.profil()
            if (response.isSuccessful) {
                Result.Success(response.body()?.data!!)
            } else {
                Result.Error(parseError(response.code()))
            }
        } catch (e: Exception) {
            Result.Error("Gagal memuat profil: ${e.message}")
        }
    }

    suspend fun getLahan(): Result<List<Lahan>> {
        return try {
            val response = api.lahan()
            if (response.isSuccessful) {
                Result.Success(response.body()?.data ?: emptyList())
            } else {
                Result.Error(parseError(response.code()))
            }
        } catch (e: Exception) {
            Result.Error("Gagal memuat lahan: ${e.message}")
        }
    }

    suspend fun getSetoran(bulan: String? = null, page: Int = 1): Result<SetoranPaginated> {
        return try {
            val response = api.setoran(bulan, page)
            if (response.isSuccessful) {
                Result.Success(response.body()?.data!!)
            } else {
                Result.Error(parseError(response.code()))
            }
        } catch (e: Exception) {
            Result.Error("Gagal memuat setoran: ${e.message}")
        }
    }

    suspend fun getRekap(tahun: String? = null): Result<RekapResponse> {
        return try {
            val response = api.rekap(tahun)
            if (response.isSuccessful) {
                Result.Success(response.body()?.data!!)
            } else {
                Result.Error(parseError(response.code()))
            }
        } catch (e: Exception) {
            Result.Error("Gagal memuat rekap: ${e.message}")
        }
    }

    private fun parseError(code: Int): String = when (code) {
        401 -> "Sesi habis, silakan login ulang"
        403 -> "Akses ditolak"
        404 -> "Data tidak ditemukan"
        422 -> "Email atau password salah"
        500 -> "Terjadi kesalahan di server"
        else -> "Error $code"
    }
}
