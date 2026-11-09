package hr.koshapp.data.repository

import hr.koshapp.data.model.HealthResponse
import hr.koshapp.data.remote.HealthApiService
import kotlinx.coroutines.CoroutineDispatcher
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlin.coroutines.cancellation.CancellationException

class HealthRepository(
    private val api: HealthApiService,
    private val ioDispatcher: CoroutineDispatcher = Dispatchers.IO
) {

    /** Mrežni poziv se uvijek izvršava izvan glavne dretve (IO dispatcher). */
    suspend fun checkHealth(): Result<HealthResponse> = withContext(ioDispatcher) {
        try {
            Result.success(api.checkHealth())
        } catch (e: CancellationException) {
            throw e
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
