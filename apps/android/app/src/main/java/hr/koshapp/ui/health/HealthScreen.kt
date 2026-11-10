package hr.koshapp.ui.health

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.stringResource
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import hr.koshapp.R
import hr.koshapp.ui.theme.KoshAppTheme

@Composable
fun HealthScreen(
    modifier: Modifier = Modifier,
    viewModel: HealthViewModel = viewModel(factory = HealthViewModel.Factory)
) {
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()
    HealthContent(
        uiState = uiState,
        onRetry = viewModel::checkHealth,
        modifier = modifier
    )
}

@Composable
fun HealthContent(
    uiState: HealthUiState,
    onRetry: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier
            .fillMaxSize()
            .padding(24.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp, Alignment.CenterVertically),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        when (uiState) {
            HealthUiState.Loading -> {
                CircularProgressIndicator()
                Text(stringResource(R.string.health_loading))
            }

            is HealthUiState.Success -> {
                Text(
                    text = stringResource(R.string.health_server_status, uiState.status.uppercase()),
                    style = MaterialTheme.typography.headlineMedium
                )
                uiState.message?.let {
                    Text(it, style = MaterialTheme.typography.bodyMedium)
                }
                Button(onClick = onRetry) { Text(stringResource(R.string.health_refresh)) }
            }

            is HealthUiState.Error -> {
                val message = if (uiState.httpCode != null) {
                    stringResource(uiState.messageRes, uiState.httpCode)
                } else {
                    stringResource(uiState.messageRes)
                }
                Text(
                    text = message,
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodyLarge,
                    textAlign = TextAlign.Center
                )
                Button(onClick = onRetry) { Text(stringResource(R.string.health_retry)) }
            }
        }
    }
}

@Preview(showBackground = true)
@Composable
private fun HealthSuccessPreview() {
    KoshAppTheme {
        HealthContent(HealthUiState.Success("ok", "API radi"), onRetry = {})
    }
}

@Preview(showBackground = true)
@Composable
private fun HealthErrorPreview() {
    KoshAppTheme {
        HealthContent(HealthUiState.Error(R.string.error_no_connection), onRetry = {})
    }
}
