package com.gmbtech.etraceability.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.gmbtech.etraceability.data.api.models.Setoran
import com.gmbtech.etraceability.data.repository.PetaniRepository
import com.gmbtech.etraceability.data.repository.Result
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.time.LocalDate
import java.time.format.DateTimeFormatter

data class SetoranUiState(
    val isLoading: Boolean = false,
    val setorans: List<Setoran> = emptyList(),
    val currentPage: Int = 1,
    val lastPage: Int = 1,
    val total: Int = 0,
    val filterBulan: String? = null,
    val error: String? = null,
)

class SetoranViewModel(private val repository: PetaniRepository) : ViewModel() {

    private val _state = MutableStateFlow(SetoranUiState())
    val state = _state.asStateFlow()

    init { load() }

    fun load(bulan: String? = _state.value.filterBulan, page: Int = 1) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, filterBulan = bulan)
            when (val result = repository.getSetoran(bulan, page)) {
                is Result.Success -> _state.value = _state.value.copy(
                    isLoading   = false,
                    setorans    = result.data.data,
                    currentPage = result.data.currentPage,
                    lastPage    = result.data.lastPage,
                    total       = result.data.total,
                    error       = null,
                )
                is Result.Error -> _state.value = _state.value.copy(
                    isLoading = false,
                    error     = result.message,
                )
                else -> Unit
            }
        }
    }

    fun setFilter(bulan: String?) {
        load(bulan = bulan, page = 1)
    }

    fun nextPage() {
        val s = _state.value
        if (s.currentPage < s.lastPage) load(page = s.currentPage + 1)
    }

    fun prevPage() {
        val s = _state.value
        if (s.currentPage > 1) load(page = s.currentPage - 1)
    }

    /** Menghasilkan list "YYYY-MM" untuk 12 bulan terakhir. */
    fun getLast12Months(): List<Pair<String, String>> {
        val fmt     = DateTimeFormatter.ofPattern("yyyy-MM")
        val fmtDisp = DateTimeFormatter.ofPattern("MMM yyyy")
        return (0..11).map { i ->
            val d = LocalDate.now().minusMonths(i.toLong())
            d.format(fmt) to d.format(fmtDisp)
        }
    }
}
