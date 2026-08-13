package com.gmbtech.etraceability.data.session

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.runBlocking

val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "session")

class SessionManager(private val context: Context) {

    companion object {
        private val KEY_TOKEN      = stringPreferencesKey("token")
        private val KEY_NAMA       = stringPreferencesKey("nama")
        private val KEY_KODE       = stringPreferencesKey("kode_petani")
        private val KEY_PETANI_ID  = stringPreferencesKey("petani_id")
    }

    val tokenFlow: Flow<String?> = context.dataStore.data.map { it[KEY_TOKEN] }
    val namaFlow: Flow<String?>  = context.dataStore.data.map { it[KEY_NAMA] }
    val kodeFlow: Flow<String?>  = context.dataStore.data.map { it[KEY_KODE] }

    /** Synchronous read — digunakan oleh OkHttp interceptor (non-suspend context). */
    fun getTokenSync(): String? = runBlocking { context.dataStore.data.first()[KEY_TOKEN] }

    suspend fun saveSession(token: String, nama: String, kode: String, petaniId: Int) {
        context.dataStore.edit { prefs ->
            prefs[KEY_TOKEN]     = token
            prefs[KEY_NAMA]      = nama
            prefs[KEY_KODE]      = kode
            prefs[KEY_PETANI_ID] = petaniId.toString()
        }
    }

    suspend fun clearSession() {
        context.dataStore.edit { it.clear() }
    }

    fun isLoggedIn(): Boolean = getTokenSync() != null
}
