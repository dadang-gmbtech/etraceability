package com.gmbtech.etraceability.ui.components

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.CornerRadius
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.gmbtech.etraceability.data.api.models.RekapBulan
import com.gmbtech.etraceability.ui.theme.*

data class BarEntry(val label: String, val value: Float, val color: Color)

/**
 * Grafik batang sederhana untuk rekap bulanan (kg per bulan).
 * Tidak membutuhkan library chart eksternal.
 */
@Composable
fun SimpleBarChart(
    data: List<RekapBulan>,
    modifier: Modifier = Modifier,
) {
    if (data.isEmpty()) return

    val maxKg = data.maxOf { it.totalKg }.toFloat().coerceAtLeast(1f)

    Column(modifier) {
        // Chart area
        Canvas(
            modifier = Modifier
                .fillMaxWidth()
                .height(160.dp)
        ) {
            val barCount = data.size
            val totalWidth = size.width
            val totalHeight = size.height
            val barWidth = (totalWidth / barCount) * 0.6f
            val gap = (totalWidth / barCount) * 0.4f / 2f
            val slotWidth = totalWidth / barCount

            data.forEachIndexed { i, entry ->
                val ratio = entry.totalKg.toFloat() / maxKg
                val barH = ratio * (totalHeight - 24.dp.toPx())
                val left = i * slotWidth + gap
                val top = totalHeight - barH - 20.dp.toPx()

                drawRoundRect(
                    color = Amber500,
                    topLeft = Offset(left, top),
                    size = Size(barWidth, barH),
                    cornerRadius = CornerRadius(4.dp.toPx()),
                )
            }

            // Baseline
            drawLine(
                color = Color(0xFFE5E7EB),
                start = Offset(0f, totalHeight - 20.dp.toPx()),
                end = Offset(totalWidth, totalHeight - 20.dp.toPx()),
                strokeWidth = 1.dp.toPx(),
            )
        }

        // X-axis labels
        Row(Modifier.fillMaxWidth()) {
            data.forEach { entry ->
                Text(
                    text = entry.bulan.takeLast(5).replace("-", "/"),
                    modifier = Modifier.weight(1f),
                    fontSize = 9.sp,
                    color = Color(0xFF9CA3AF),
                    textAlign = androidx.compose.ui.text.style.TextAlign.Center,
                )
            }
        }
    }
}
