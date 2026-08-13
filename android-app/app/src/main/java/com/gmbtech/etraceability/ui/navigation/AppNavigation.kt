package com.gmbtech.etraceability.ui.navigation

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.ListAlt
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.*
import com.gmbtech.etraceability.data.repository.PetaniRepository
import com.gmbtech.etraceability.data.session.SessionManager
import com.gmbtech.etraceability.ui.screen.DashboardScreen
import com.gmbtech.etraceability.ui.screen.LoginScreen
import com.gmbtech.etraceability.ui.screen.SetoranScreen
import com.gmbtech.etraceability.ui.theme.Amber600
import com.gmbtech.etraceability.ui.viewmodel.DashboardViewModel
import com.gmbtech.etraceability.ui.viewmodel.LoginViewModel
import com.gmbtech.etraceability.ui.viewmodel.SetoranViewModel

sealed class Screen(val route: String) {
    object Login     : Screen("login")
    object Main      : Screen("main")
    object Dashboard : Screen("dashboard")
    object Setoran   : Screen("setoran")
}

data class BottomNavItem(val label: String, val icon: ImageVector, val route: String)

private val bottomNavItems = listOf(
    BottomNavItem("Dashboard", Icons.Default.Dashboard,  Screen.Dashboard.route),
    BottomNavItem("Setoran",   Icons.Default.ListAlt,    Screen.Setoran.route),
)

@Composable
fun AppNavigation(repository: PetaniRepository, session: SessionManager) {
    val rootNav = rememberNavController()
    val startDest = if (session.isLoggedIn()) Screen.Main.route else Screen.Login.route

    NavHost(navController = rootNav, startDestination = startDest) {

        // ── Login ──
        composable(Screen.Login.route) {
            val vm = remember { LoginViewModel(repository) }
            LoginScreen(vm) {
                rootNav.navigate(Screen.Main.route) {
                    popUpTo(Screen.Login.route) { inclusive = true }
                }
            }
        }

        // ── Main (Bottom Nav) ──
        composable(Screen.Main.route) {
            val bottomNav = rememberNavController()
            val dashboardVm = remember { DashboardViewModel(repository, session) }
            val setoranVm   = remember { SetoranViewModel(repository) }

            Scaffold(
                bottomBar = {
                    NavigationBar(containerColor = Amber600) {
                        val backStack by bottomNav.currentBackStackEntryAsState()
                        val current = backStack?.destination?.route
                        bottomNavItems.forEach { item ->
                            NavigationBarItem(
                                selected = current == item.route,
                                onClick  = {
                                    bottomNav.navigate(item.route) {
                                        popUpTo(bottomNav.graph.findStartDestination().id) { saveState = true }
                                        launchSingleTop = true
                                        restoreState = true
                                    }
                                },
                                icon    = { Icon(item.icon, item.label) },
                                label   = { Text(item.label) },
                                colors  = NavigationBarItemDefaults.colors(
                                    selectedIconColor   = Amber600,
                                    unselectedIconColor = androidx.compose.ui.graphics.Color.White,
                                    unselectedTextColor = androidx.compose.ui.graphics.Color.White,
                                    indicatorColor      = androidx.compose.ui.graphics.Color.White,
                                ),
                            )
                        }
                    }
                }
            ) { innerPadding ->
                NavHost(
                    navController = bottomNav,
                    startDestination = Screen.Dashboard.route,
                    modifier = Modifier.padding(innerPadding),
                ) {
                    composable(Screen.Dashboard.route) {
                        DashboardScreen(dashboardVm) {
                            rootNav.navigate(Screen.Login.route) {
                                popUpTo(Screen.Main.route) { inclusive = true }
                            }
                        }
                    }
                    composable(Screen.Setoran.route) {
                        SetoranScreen(setoranVm)
                    }
                }
            }
        }
    }
}
