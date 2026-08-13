package com.gmbtech.etraceability.ui.screen

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.gmbtech.etraceability.data.api.models.Setoran
import com.gmbtech.etraceability.ui.theme.*
import com.gmbtech.etraceability.ui.viewmodel.SetoranViewModel
import java.text.NumberFormat
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SetoranScreen(viewModel: SetoranViewModel) {
    val state by viewModel.state.collectAsState()
    val idr = NumberFormat.getNumberInstance(Locale("id", "ID"))

    var showFilterSheet by remember { mutableStateOf(false) }
    val bulanOptions = remember { viewModel.getLast12Months() }

    if (showFilterSheet) {
        ModalBottomSheet(onDismissRequest = { showFilterSheet = false }) {
            Column(Modifier.padding(16.dp)) {
                Text("Filter Bulan", fontWeight = FontWeight.SemiBold, fontSize = 16.sp)
                Spacer(Modifier.height(12.dp))
                // Semua
                FilterChip(
                    selected = state.filterBulan == null,
                    onClick  = { viewModel.setFilter(null); showFilterSheet = false },
                    label    = { Text("Semua Bulan") },
                )
                Spacer(Modifier.height(8.dp))
                bulanOptions.forEach { (kode, label) ->
                    FilterChip(
                        selected = state.filterBulan == kode,
                        onClick  = { viewModel.setFilter(kode); showFilterSheet = false },
                        label    = { Text(label) },
                        modifier = Modifier.padding(vertical = 2.dp),
                    )
                }
                Spacer(Modifier.height(24.dp))
            }
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text("Riwayat Setoran", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                        state.filterBulan?.let { Text("Bulan: $it", fontSize = 11.sp, color = Color(0xCCFFFFFF)) }
                            ?: Text("Semua Waktu · ${state.total} setoran", fontSize = 11.sp, color = Color(0xCCFFFFFF))
                    }
                },
                actions = {
                    IconButton(onClick = { showFilterSheet = true }) {
                        Icon(Icons.Default.FilterList, "Filter")
                    }
                    IconButton(onClick = { viewModel.load() }) {
                        Icon(Icons.Default.Refresh, "Refresh")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Amber600,
                    titleContentColor = Color.White,
                    actionIconContentColor = Color.White,
                ),
            )
        },
        containerColor = Gray50,
    ) { padding ->
        Box(Modifier.fillMaxSize().padding(padding)) {
            when {
                state.isLoading -> CircularProgressIndicator(Modifier.align(Alignment.Center))
                state.error != null -> {
                    Column(Modifier.align(Alignment.Center), horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(state.error!!, color = Red500)
                        Spacer(Modifier.height(8.dp))
                        Button(onClick = { viewModel.load() }) { Text("Coba Lagi") }
                    }
                }
                state.setorans.isEmpty() -> {
                    Text(
                        "Belum ada data setoran",
                        Modifier.align(Alignment.Center),
                        color = Color(0xFF9CA3AF),
                    )
                }
                else -> {
                    LazyColumn(
                        Modifier.fillMaxSize(),
                        contentPadding = PaddingValues(16.dp),
                        verticalArrangement = Arrangement.spacedBy(8.dp),
                    ) {
                        items(state.setorans) { setoran ->
                            SetoranCard(setoran, idr)
                        }

                        // Pagination
                        item {
                            Row(
                                Modifier.fillMaxWidth().padding(vertical = 8.dp),
                                horizontalArrangement = Arrangement.SpaceBetween,
                                verticalAlignment = Alignment.CenterVertically,
                            ) {
                                TextButton(
                                    onClick = { viewModel.prevPage() },
                                    enabled = state.currentPage > 1,
                                ) { Text("← Sebelumnya") }

                                Text(
                                    "Hal. ${state.currentPage} / ${state.lastPage}",
                                    fontSize = 12.sp,
                                    color = Gray600,
                                )

                                TextButton(
                                    onClick = { viewModel.nextPage() },
                                    enabled = state.currentPage < state.lastPage,
                                ) { Text("Berikutnya →") }
                            }
                        }

                        item { Spacer(Modifier.height(8.dp)) }
                    }
                }
            }
        }
    }
}

@Composable
private fun SetoranCard(setoran: Setoran, idr: NumberFormat) {
    val (produkLabel, produkColor) = when (setoran.jenisProduk) {
        "gula_semut" -> "Gula Semut" to Green500
        "raw_sugar"  -> "Raw Sugar"  to Blue500
        "nira"       -> "Nira"       to Purple500
        "gula_cair"  -> "Gula Cair"  to Color(0xFFF97316)
        else         -> setoran.jenisProduk to Gray600
    }

    Card(
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(1.dp),
    ) {
        Column(Modifier.padding(14.dp)) {
            Row(
                Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                // Produk badge
                Surface(
                    shape = RoundedCornerShape(6.dp),
                    color = produkColor.copy(alpha = 0.12f),
                ) {
                    Text(
                        produkLabel,
                        Modifier.padding(horizontal = 8.dp, vertical = 3.dp),
                        fontSize = 12.sp,
                        color = produkColor,
                        fontWeight = FontWeight.SemiBold,
                    )
                }

                // Tanggal
                Text(
                    setoran.tanggalSetor.take(10),
                    fontSize = 12.sp,
                    color = Gray600,
                )
            }

            Spacer(Modifier.height(8.dp))

            Row(
                Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
            ) {
                Column {
                    Text(
                        "${idr.format(setoran.beratKg.toLong())} kg",
                        fontWeight = FontWeight.Bold,
                        fontSize = 18.sp,
                        color = Gray800,
                    )
                    setoran.hariAkumulasi?.let {
                        Text("$it hari akumulasi", fontSize = 11.sp, color = Gray600)
                    }
                }
                Column(horizontalAlignment = Alignment.End) {
                    Text(
                        "Rp ${idr.format(setoran.totalHarga.toLong())}",
                        fontWeight = FontWeight.Bold,
                        fontSize = 15.sp,
                        color = Green500,
                    )
                }
            }

            // Anomali warning
            if (setoran.isAnoali) {
                Spacer(Modifier.height(6.dp))
                Row(
                    Modifier
                        .fillMaxWidth()
                        .background(Red100, RoundedCornerShape(6.dp))
                        .padding(horizontal = 8.dp, vertical = 5.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Icon(Icons.Default.Warning, null, tint = Red500, modifier = Modifier.size(14.dp))
                    Spacer(Modifier.width(4.dp))
                    Text(
                        setoran.keteranganAnoali ?: "Terdeteksi anomali",
                        fontSize = 11.sp,
                        color = Red500,
                    )
                }
            }
        }
    }
}
