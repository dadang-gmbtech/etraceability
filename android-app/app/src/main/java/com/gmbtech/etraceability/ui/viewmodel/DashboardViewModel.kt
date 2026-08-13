package com.gmbtech.etraceability.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.gmbtech.etraceability.data.api.models.Lahan
import com.gmbtech.etraceability.data.api.models.Profil
import com.gmbtech.etraceability.data.api.models.RekapResponse
import com.gmbtech.etraceability.data.repository.PetaniRepository
import com.gmbtech.etraceability.data.repository.Result
import com.gmbtech.etraceability.data.session.SessionManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

data class DashboardUiState(
    val isLoading: Boolean = false,
    val profil: Profil? = null,
    val lahans: List<Lahan> = emptyList(),
    val rekap: RekapResponse? = null,
    val error: String? = null,
)

class DashboardViewModel(
    private val repository: PetaniRepository,
    val session: SessionManager,
) : ViewModel() {

    private val _state = MutableStateFlow(DashboardUiState())
    val state = _state.asStateFlow()

    init { load() }

    fun load() {
        viewModelScope.launch {
            _state.value = DashboardUiState(isLoading = true)

            val profilResult = repository.getProfil()
            val lahanResult  = repository.getLahan()
            val rekapResult  = repository.getRekap()

            _state.value = DashboardUiState(
                profil = (profilResult as? Result.Success)?.data,
                lahans = (lahanResult  as? Result.Success)?.data ?: emptyList(),
                rekap  = (rekapResult  as? Result.Success)?.data,
                error  = (profilResult as? Result.Error)?.message
                    ?: (rekapResult  as? Result.Error)?.message,
            )
        }
    }

    fun logout(onDone: () -> Unit) {
        viewModelScope.launch {
            repository.logout()
            onDone()
        }
    }
}
