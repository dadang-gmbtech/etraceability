package com.gmbtech.etraceability.ui.screen

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ExitToApp
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.gmbtech.etraceability.data.api.models.RekapBulan
import com.gmbtech.etraceability.ui.components.SimpleBarChart
import com.gmbtech.etraceability.ui.components.StatCard
import com.gmbtech.etraceability.ui.theme.*
import com.gmbtech.etraceability.ui.viewmodel.DashboardViewModel
import java.text.NumberFormat
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DashboardScreen(
    viewModel: DashboardViewModel,
    onLogout: () -> Unit,
) {
    val state by viewModel.state.collectAsState()
    val namaFlow by viewModel.session.namaFlow.collectAsState(initial = null)
    var showLogoutDialog by remember { mutableStateOf(false) }

    // Auto-redirect to login when session expired
    LaunchedEffect(state.sessionExpired) {
        if (state.sessionExpired) onLogout()
    }

    val idr = NumberFormat.getNumberInstance(Locale("id", "ID"))

    if (showLogoutDialog) {
        AlertDialog(
            onDismissRequest = { showLogoutDialog = false },
            title = { Text("Keluar?") },
            text  = { Text("Anda akan keluar dari akun ini.") },
            confirmButton = {
                TextButton(onClick = {
                    showLogoutDialog = false
                    viewModel.logout(onLogout)
                }) { Text("Keluar", color = Red500) }
            },
            dismissButton = {
                TextButton(onClick = { showLogoutDialog = false }) { Text("Batal") }
            }
        )
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text("Dashboard", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                        namaFlow?.let { Text(it, fontSize = 12.sp, color = Color(0xCC000000)) }
                    }
                },
                actions = {
                    IconButton(onClick = { viewModel.load() }) {
                        Icon(Icons.Default.Refresh, "Muat Ulang")
                    }
                    IconButton(onClick = { showLogoutDialog = true }) {
                        Icon(Icons.Default.ExitToApp, "Keluar")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Amber600,
                    titleContentColor = Color.White,
                    actionIconContentColor = Color.White,
                )
            )
        },
        containerColor = Gray50,
    ) { padding ->
        Box(Modifier.fillMaxSize().padding(padding)) {
            when {
                state.isLoading -> CircularProgressIndicator(Modifier.align(Alignment.Center))
                state.error != null && state.profil == null -> {
                    Column(Modifier.align(Alignment.Center), horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(state.error!!, color = Red500)
                        Spacer(Modifier.height(8.dp))
                        Button(onClick = { viewModel.load() }) { Text("Coba Lagi") }
                    }
                }
                else -> {
                    Column(
                        Modifier
                            .fillMaxSize()
                            .verticalScroll(rememberScrollState())
                            .padding(16.dp),
                        verticalArrangement = Arrangement.spacedBy(16.dp),
                    ) {
                        // ── Info Petani ──
                        state.profil?.let { p ->
                            Card(
                                shape = RoundedCornerShape(12.dp),
                                colors = CardDefaults.cardColors(containerColor = Amber600),
                            ) {
                                Column(Modifier.padding(16.dp)) {
                                    Row(
                                        Modifier.fillMaxWidth(),
                                        horizontalArrangement = Arrangement.SpaceBetween,
                                    ) {
                                        Column {
                                            Text(p.nama, fontWeight = FontWeight.Bold, fontSize = 18.sp, color = Color.White)
                                            Text(p.kodePetani, fontSize = 13.sp, color = Color(0xCCFFFFFF))
                                        }
                                        if (p.aktif) {
                                            Surface(
                                                shape = RoundedCornerShape(20.dp),
                                                color = Green500,
                                            ) {
                                                Text(
                                                    "Aktif",
                                                    modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
                                                    color = Color.White,
                                                    fontSize = 12.sp,
                                                    fontWeight = FontWeight.SemiBold,
                                                )
                                            }
                                        }
                                    }
                                    p.desa?.let { d ->
                                        Spacer(Modifier.height(4.dp))
                                        Text(
                                            "${d}${p.kecamatan?.let { ", $it" } ?: ""}",
                                            fontSize = 12.sp,
                                            color = Color(0xBBFFFFFF),
                                        )
                                    }
                                }
                            }
                        }

                        // ── Stat Cards ──
                        state.rekap?.total?.let { t ->
                            Text("Ringkasan Total", fontWeight = FontWeight.SemiBold, color = Gray600)
                            Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                                    StatCard(
                                        label = "TOTAL SETORAN",
                                        value = "${idr.format(t.totalKg.toLong())} kg",
                                        accentColor = Amber500,
                                        modifier = Modifier.weight(1f).height(90.dp),
                                    )
                                    StatCard(
                                        label = "JUMLAH TRANSAKSI",
                                        value = "${t.jumlahSetor}×",
                                        accentColor = Blue500,
                                        modifier = Modifier.weight(1f).height(90.dp),
                                    )
                                }
                                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                                    StatCard(
                                        label = "HASIL PENJUALAN",
                                        value = "Rp ${idr.format(t.totalHarga.toLong())}",
                                        accentColor = Green500,
                                        modifier = Modifier.weight(1f).height(90.dp),
                                    )
                                    StatCard(
                                        label = "ANOMALI",
                                        value = "${t.jumlahAnoali}",
                                        sub = "dari ${t.jumlahSetor} setoran",
                                        accentColor = if (t.jumlahAnoali > 0) Red500 else Green500,
                                        modifier = Modifier.weight(1f).height(90.dp),
                                    )
                                }
                            }
                        }

                        // ── Lahan ──
                        if (state.lahans.isNotEmpty()) {
                            Text("Data Lahan", fontWeight = FontWeight.SemiBold, color = Gray600)
                            Card(shape = RoundedCornerShape(12.dp), colors = CardDefaults.cardColors(containerColor = Color.White)) {
                                Column {
                                    state.lahans.forEachIndexed { i, l ->
                                        Row(
                                            Modifier
                                                .fillMaxWidth()
                                                .padding(horizontal = 16.dp, vertical = 12.dp),
                                            horizontalArrangement = Arrangement.SpaceBetween,
                                        ) {
                                            Column {
                                                Text(l.kodeLahan, fontWeight = FontWeight.SemiBold, fontSize = 14.sp)
                                                l.namaLahan?.let { Text(it, fontSize = 12.sp, color = Gray600) }
                                            }
                                            Text("${l.pohonDiDeres} pohon", fontSize = 13.sp, color = Amber600, fontWeight = FontWeight.Medium)
                                        }
                                        if (i < state.lahans.lastIndex)
                                            HorizontalDivider(color = Gray100)
                                    }
                                }
                            }
                        }

                        // ── Grafik Rekap Bulanan ──
                        state.rekap?.bulanan?.let { bulanan ->
                            if (bulanan.isNotEmpty()) {
                                Text("Setoran per Bulan (kg)", fontWeight = FontWeight.SemiBold, color = Gray600)
                                Card(
                                    shape = RoundedCornerShape(12.dp),
                                    colors = CardDefaults.cardColors(containerColor = Color.White),
                                ) {
                                    Column(Modifier.padding(16.dp)) {
                                        SimpleBarChart(
                                            data = bulanan.reversed(),
                                            modifier = Modifier.fillMaxWidth(),
                                        )
                                    }
                                }

                                // Tabel rekap bulanan
                                Card(
                                    shape = RoundedCornerShape(12.dp),
                                    colors = CardDefaults.cardColors(containerColor = Color.White),
                                ) {
                                    Column {
                                        // Header
                                        Row(
                                            Modifier
                                                .fillMaxWidth()
                                                .background(Gray100)
                                                .padding(horizontal = 12.dp, vertical = 8.dp),
                                        ) {
                                            Text("Bulan", Modifier.weight(2f), fontSize = 11.sp, color = Gray600, fontWeight = FontWeight.SemiBold)
                                            Text("Kg", Modifier.weight(1f), fontSize = 11.sp, color = Gray600, fontWeight = FontWeight.SemiBold)
                                            Text("Rp", Modifier.weight(2f), fontSize = 11.sp, color = Gray600, fontWeight = FontWeight.SemiBold)
                                        }
                                        bulanan.forEach { r ->
                                            Row(
                                                Modifier
                                                    .fillMaxWidth()
                                                    .padding(horizontal = 12.dp, vertical = 8.dp),
                                            ) {
                                                Text(r.bulan, Modifier.weight(2f), fontSize = 12.sp)
                                                Text(idr.format(r.totalKg.toLong()), Modifier.weight(1f), fontSize = 12.sp)
                                                Text(
                                                    idr.format(r.totalHarga.toLong()),
                                                    Modifier.weight(2f),
                                                    fontSize = 12.sp,
                                                    color = Green500,
                                                    fontWeight = FontWeight.Medium,
                                                )
                                            }
                                            HorizontalDivider(color = Gray100)
                                        }
                                    }
                                }
                            }
                        }

                        Spacer(Modifier.height(16.dp))
                    }
                }
            }
        }
    }
}
