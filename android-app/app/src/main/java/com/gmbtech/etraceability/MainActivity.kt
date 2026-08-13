package com.gmbtech.etraceability

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import com.gmbtech.etraceability.data.api.ApiClient
import com.gmbtech.etraceability.data.repository.PetaniRepository
import com.gmbtech.etraceability.data.session.SessionManager
import com.gmbtech.etraceability.ui.navigation.AppNavigation
import com.gmbtech.etraceability.ui.theme.ETraceabilityTheme

class MainActivity : ComponentActivity() {

    private lateinit var session: SessionManager
    private lateinit var repository: PetaniRepository

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        session    = SessionManager(applicationContext)
        ApiClient.init(session)
        repository = PetaniRepository(session)

        setContent {
            ETraceabilityTheme {
                AppNavigation(repository, session)
            }
        }
    }
}
