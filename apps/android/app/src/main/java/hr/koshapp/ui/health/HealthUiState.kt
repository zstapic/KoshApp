package hr.koshapp.ui.health

import androidx.annotation.StringRes

sealed interface HealthUiState {
    data object Loading : HealthUiState
    data class Success(val status: String, val message: String?) : HealthUiState
    data class Error(@get:StringRes val messageRes: Int, val httpCode: Int? = null) : HealthUiState
}
