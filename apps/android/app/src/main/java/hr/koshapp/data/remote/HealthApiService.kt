package hr.koshapp.data.remote

import hr.koshapp.data.model.HealthResponse
import retrofit2.http.GET

interface HealthApiService {

    @GET("api/health")
    suspend fun checkHealth(): HealthResponse
}