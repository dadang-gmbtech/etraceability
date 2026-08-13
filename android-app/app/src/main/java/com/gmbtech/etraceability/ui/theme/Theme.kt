package com.gmbtech.etraceability.ui.theme

import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

val Amber500  = Color(0xFFF59E0B)
val Amber600  = Color(0xFFD97706)
val Amber100  = Color(0xFFFEF3C7)
val Green500  = Color(0xFF10B981)
val Green100  = Color(0xFFD1FAE5)
val Red500    = Color(0xFFEF4444)
val Red100    = Color(0xFFFEE2E2)
val Blue500   = Color(0xFF3B82F6)
val Blue100   = Color(0xFFDBEAFE)
val Purple500 = Color(0xFF8B5CF6)
val Purple100 = Color(0xFFEDE9FE)
val Gray50    = Color(0xFFF9FAFB)
val Gray100   = Color(0xFFF3F4F6)
val Gray200   = Color(0xFFE5E7EB)
val Gray600   = Color(0xFF4B5563)
val Gray800   = Color(0xFF1F2937)

private val LightColors = lightColorScheme(
    primary        = Amber600,
    onPrimary      = Color.White,
    primaryContainer    = Amber100,
    onPrimaryContainer  = Color(0xFF78350F),
    secondary           = Green500,
    onSecondary         = Color.White,
    background          = Gray50,
    onBackground        = Gray800,
    surface             = Color.White,
    onSurface           = Gray800,
    error               = Red500,
    onError             = Color.White,
)

@Composable
fun ETraceabilityTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = LightColors,
        typography  = Typography(),
        content     = content,
    )
}
