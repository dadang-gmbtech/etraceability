package com.gmbtech.etraceability.data.api

import com.gmbtech.etraceability.data.api.models.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    @POST("v1/petani/login")
    suspend fun login(@Body body: LoginRequest): Response<LoginResponse>

    @GET("v1/petani/profil")
    suspend fun profil(): Response<ApiResponse<Profil>>

    @GET("v1/petani/lahan")
    suspend fun lahan(): Response<ApiResponse<List<Lahan>>>

    @GET("v1/petani/setoran")
    suspend fun setoran(
        @Query("bulan") bulan: String? = null,
        @Query("page") page: Int = 1,
    ): Response<ApiResponse<SetoranPaginated>>

    @GET("v1/petani/rekap")
    suspend fun rekap(
        @Query("tahun") tahun: String? = null,
    ): Response<ApiResponse<RekapResponse>>
}
