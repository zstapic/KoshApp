package hr.koshapp

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.ui.Modifier
import hr.koshapp.ui.health.HealthScreen
import hr.koshapp.ui.theme.KoshAppTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            KoshAppTheme {
                Scaffold(modifier = Modifier.fillMaxSize()) { innerPadding ->
                    HealthScreen(modifier = Modifier.padding(innerPadding))
                }
            }
        }
    }
}
