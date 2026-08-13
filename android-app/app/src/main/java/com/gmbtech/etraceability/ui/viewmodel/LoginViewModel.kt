package com.gmbtech.etraceability.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.gmbtech.etraceability.data.repository.PetaniRepository
import com.gmbtech.etraceability.data.repository.Result
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

data class LoginUiState(
    val isLoading: Boolean = false,
    val error: String? = null,
    val isSuccess: Boolean = false,
)

class LoginViewModel(private val repository: PetaniRepository) : ViewModel() {

    private val _state = MutableStateFlow(LoginUiState())
    val state = _state.asStateFlow()

    fun login(email: String, password: String) {
        if (email.isBlank() || password.isBlank()) {
            _state.value = LoginUiState(error = "Email dan password tidak boleh kosong")
            return
        }
        viewModelScope.launch {
            _state.value = LoginUiState(isLoading = true)
            when (val result = repository.login(email.trim(), password)) {
                is Result.Success -> _state.value = LoginUiState(isSuccess = true)
                is Result.Error   -> _state.value = LoginUiState(error = result.message)
                else              -> Unit
            }
        }
    }

    fun clearError() {
        _state.value = _state.value.copy(error = null)
    }
}
