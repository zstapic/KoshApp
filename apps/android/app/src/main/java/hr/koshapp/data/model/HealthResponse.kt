package hr.koshapp.data.model

// ako server izostavi polje, aplikacija se ne smije srušiti.
data class HealthResponse(
    val status: String?,
    val message: String?,
    val time: String?
)