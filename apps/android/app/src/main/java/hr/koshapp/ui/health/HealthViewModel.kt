package hr.koshapp.ui.health

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.initializer
import androidx.lifecycle.viewmodel.viewModelFactory
import com.google.gson.JsonParseException
import hr.koshapp.R
import hr.koshapp.data.remote.RetrofitClient
import hr.koshapp.data.repository.HealthRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import retrofit2.HttpException
import java.io.IOException
import java.net.ConnectException
import java.net.SocketTimeoutException
import java.net.UnknownHostException

class HealthViewModel(
    private val repository: HealthRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<HealthUiState>(HealthUiState.Loading)
    val uiState: StateFlow<HealthUiState> = _uiState.asStateFlow()

    init {
        checkHealth()
    }

    fun checkHealth() {
        _uiState.value = HealthUiState.Loading
        viewModelScope.launch {
            _uiState.value = repository.checkHealth().fold(
                onSuccess = { response ->
                    HealthUiState.Success(
                        status = response.status ?: "?",
                        message = response.message
                    )
                },
                onFailure = { it.toErrorState() }
            )
        }
    }

    private fun Throwable.toErrorState(): HealthUiState.Error = when (this) {
        is SocketTimeoutException -> HealthUiState.Error(R.string.error_timeout)
        is UnknownHostException, is ConnectException -> HealthUiState.Error(R.string.error_no_connection)
        is HttpException -> HealthUiState.Error(R.string.error_http, httpCode = code())
        is JsonParseException -> HealthUiState.Error(R.string.error_invalid_response)
        is IOException -> HealthUiState.Error(R.string.error_no_connection)
        else -> HealthUiState.Error(R.string.error_unknown)
    }

    companion object {
        val Factory: ViewModelProvider.Factory = viewModelFactory {
            initializer {
                HealthViewModel(HealthRepository(RetrofitClient.healthApi))
            }
        }
    }
}
