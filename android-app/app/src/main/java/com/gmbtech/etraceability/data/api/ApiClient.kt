package com.gmbtech.etraceability.data.api

import com.gmbtech.etraceability.data.session.SessionManager
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {

    // ─────────────────────────────────────────────────────────────────────
    // Ganti BASE_URL dengan URL server Anda:
    //   • Emulator Android  → http://10.0.2.2/etraceability/api/
    //   • Perangkat fisik   → http://<IP-LAN-Anda>/etraceability/api/
    //   • Production        → https://domain-anda.com/api/
    // ─────────────────────────────────────────────────────────────────────
    const val BASE_URL = "https://traceability.riset.unsoed.ac.id/api/"

    private var sessionManager: SessionManager? = null

    fun init(sm: SessionManager) {
        sessionManager = sm
    }

    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    private val client: OkHttpClient by lazy {
        OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .addInterceptor { chain ->
                val token = sessionManager?.getTokenSync()
                val request = if (token != null) {
                    chain.request().newBuilder()
                        .addHeader("Authorization", "Bearer $token")
                        .addHeader("Accept", "application/json")
                        .build()
                } else {
                    chain.request().newBuilder()
                        .addHeader("Accept", "application/json")
                        .build()
                }
                chain.proceed(request)
            }
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .build()
    }

    val service: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
