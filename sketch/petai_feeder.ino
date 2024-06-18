// Include Libraries
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <AccelStepper.h>
#include <SoftwareSerial.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include "esp_camera.h"
#include "DFRobot_URM09.h"
#include "DHTesp.h"

// GPIO Pins ---------------------------------------------------------------------
#define PWDN_GPIO_NUM 32
#define RESET_GPIO_NUM -1
#define XCLK_GPIO_NUM 0
#define SIOD_GPIO_NUM 26
#define SIOC_GPIO_NUM 27
#define Y9_GPIO_NUM 35
#define Y8_GPIO_NUM 34
#define Y7_GPIO_NUM 39
#define Y6_GPIO_NUM 36
#define Y5_GPIO_NUM 21
#define Y4_GPIO_NUM 19
#define Y3_GPIO_NUM 18
#define Y2_GPIO_NUM 5
#define VSYNC_GPIO_NUM 25
#define HREF_GPIO_NUM 23
#define PCLK_GPIO_NUM 22

// LED Flash PIN (GPIO 4)
#define FLASH_LED_PIN 4

// Stepper Pins
#define DIR_PIN 13
#define STEP_PIN 15
#define EN_PIN 2
#define MOTOR_TYPE 1

// Sensor Pins
#define ANALOG_IN_PIN 4
#define DHT_PIN 2

// Initialize Sensors
DFRobot_URM09 URM09_1;
DFRobot_URM09 URM09_2;
DHTesp dht;
dht.setup(DHT_PIN);

float adc_voltage = 0.0;
float in_voltage = 0.0;
float R1 = 30000.0;
float R2 = 7500.0; 
float ref_voltage = 5.0;
int adc_value = 0;

// Wifi and server settings ------------------------------------------------------
const char* ssid = "########";
const char* password = "########"
String serverName = "########";
const int serverPort = 443;
String stationCode = "########";

// API paths ---------------------------------------------------------------------
String recordPath = "########";
String uploadPath = "########";
String logQuery = "";

// Photo settings ----------------------------------------------------------------
unsigned long previousMillis = 0;
const int Interval = 20000;  //--> Photo capture every 20 seconds
bool LED_Flash_ON = true;

// Initialize WiFiClientSecure
WiFiClientSecure client;

// Initialize Stepper
AccelStepper stepper = AccelStepper(MOTOR_TYPE, STEP_PIN, DIR_PIN);
const int maxSpeed = 2000;
const int accel = 800;

void setup() {
  // Disable brownout detector.
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  //Initialize serial and wait for port to open:
  Serial.begin(115200);
  delay(100);

  while(!URM09_1.begin(0x10)){
    Serial.println("URM09_1 device number error");
    delay(1000);
  }
  while(!URM09_2.begin(0x11)){
    Serial.println("URM09_2 device number error");
    delay(1000);
  }

  // Connecting to Wifi
  connectToWiFi();

  URM09_1.setModeRange(MEASURE_MODE_AUTOMATIC ,MEASURE_RANG_500);
  URM09_2.setModeRange(MEASURE_MODE_AUTOMATIC ,MEASURE_RANG_500);
  initializeCam(); // Initialize ESP-Cam
  initializeStepper(); // Initialize Stepper
}

void loop() {
  runFeeder();
  delay(1000);
}

String sendData(String query) {
  HTTPClient http;
  float temp = dht.getTemperature();
  float humid = dht.getHumidity();
  int16_t dist1 = URM09_1.getDistance();
  int16_t dist2 = URM09_2.getDistance();
  float volt = getVoltage();

  logQuery += "temp=" + test + "&";
  logQuery += "humid=" + humid + "&";
  logQuery += "volt=" + volt + "&";
  logQuery += "tanklvl=" + dist1 + "&";
  logQuery += "platelvl=" + dist2;
  http.begin("https://" + serverName + recordPath + "?code=" + stationCode + "&" + query);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  int httpCode = http.GET();

  if (httpCode > 0) {
    // file found at server
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      return payload;
    } else {
      return String(httpCode);
    }
  } else {
    return http.errorToString(httpCode).c_str();
  }

  http.end();
}

void initializeStepper() {
  pinMode(EN_PIN, OUTPUT);
  digitalWrite(EN_PIN, LOW);
  // Set the maximum speed and acceleration:
  stepper.setMaxSpeed(maxSpeed);
  stepper.setAcceleration(accel);
}

void runFeeder() {
  String condition = sendData("action=check");
  if (condition == "true") {
    runStepper();
    String query = "action=run";
    sendData(query);
  } else {
    Serial.println(condition);
  }
}

void runStepper() {
  digitalWrite(EN_PIN, LOW);
  // Set the target position:
  stepper.moveTo(0);
  // Run to target position with set speed and acceleration/deceleration:
  stepper.runToPosition();

  digitalWrite(EN_PIN, HIGH);
  delay(2000);

  digitalWrite(EN_PIN, LOW);
  // Move back to zero:
  stepper.moveTo(6400);
  stepper.runToPosition();

  digitalWrite(EN_PIN, HIGH);
  delay(2000);
}

void connectToWiFi() {
  Serial.print("Attempting to connect to SSID: ");
  Serial.println(ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  // attempt to connect to Wifi network:
  int connecting_process_timed_out = 20;  //--> 20 = 20 seconds.
  connecting_process_timed_out = connecting_process_timed_out * 2;
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    // wait 1 second for re-trying
    delay(500);
    if (connecting_process_timed_out > 0) connecting_process_timed_out--;
    if (connecting_process_timed_out == 0) {
      Serial.println();
      Serial.print("Failed to connect to ");
      Serial.println(ssid);
      Serial.println("Restarting the ESP32 CAM.");
      delay(1000);
      ESP.restart();
    }
  }

  Serial.println();
  Serial.print("Successfully connected to ");
  Serial.println(ssid);

  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void initializeCam() {
  Serial.println();
  Serial.println("Set the camera ESP32 CAM...");

  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  // init with high specs to pre-allocate larger buffers
  if (psramFound()) {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 10;  //0-63 lower number means higher quality
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_CIF;
    config.jpeg_quality = 12;  //0-63 lower number means higher quality
    config.fb_count = 1;
  }

  // Initialize the Camera
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    Serial.println();
    Serial.println("Restarting the ESP32 CAM.");
    delay(1000);
    ESP.restart();
  }

  Serial.println("Set camera ESP32 CAM successfully.");
  Serial.println();
}

void sendPhotoToServer() {
  String AllData;
  String DataBody;

  Serial.println("Taking a photo...");

  for (int i = 0; i <= 3; i++) {
    camera_fb_t* fb = NULL;
    fb = esp_camera_fb_get();
    if (!fb) {
      Serial.println("Camera capture failed");
      Serial.println("Restarting the ESP32 CAM.");
      delay(1000);
      ESP.restart();
    }
    esp_camera_fb_return(fb);
    delay(200);
  }

  camera_fb_t* fb = NULL;
  fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed");
    Serial.println("Restarting the ESP32 CAM.");
    delay(1000);
    ESP.restart();
  }

  Serial.println("Taking a photo was successful.");
  //----------------------------------------

  Serial.println("Connecting to server: " + serverName);

  String post_data = "--dataMarker\r\n";
  post_data += "Content-Disposition: form-data; name=\"imageFile\"; filename=\"ESP32CAMCap.jpg\"\r\n";
  post_data += "Content-Type: image/jpeg\r\n\r\n";

  String boundary = "\r\n--dataMarker--\r\n";

  uint32_t imageLen = fb->len;
  uint32_t dataLen = post_data.length() + boundary.length();
  uint32_t totalLen = imageLen + dataLen;

  HTTPClient http;

  http.begin("https://" + serverName + uploadPath + "?code=" + stationCode);
  http.addHeader("Content-Type", "multipart/form-data; boundary=dataMarker");
  http.addHeader("Content-Length", String(totalLen));

  String payload = post_data;
  uint8_t* fbBuf = fb->buf;
  size_t fbLen = fb->len;

  for (size_t n = 0; n < fbLen; n += 1024) {
    size_t bytesRemaining = fbLen - n;
    size_t bytesToAdd = (bytesRemaining > 1024) ? 1024 : bytesRemaining;
    payload += String((const char*)(fbBuf + n), bytesToAdd);
  }

  payload += boundary;

  esp_camera_fb_return(fb);
  int httpCode = http.POST(payload);

  if (httpCode > 0) {
    // file found at server
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println(payload);
      return;
    } else {
      Serial.println(String(httpCode));
      return;
    }
  } else {
    Serial.println(http.errorToString(httpCode).c_str());
    return;
  }

  http.end();
}

float getVoltage() {
  // Read the Analog Input
  adc_value = analogRead(ANALOG_IN_PIN);
  
  // Determine voltage at ADC input
  adc_voltage  = (adc_value * ref_voltage) / 1024.0;
  
  // Calculate voltage at divider input
  in_voltage = adc_voltage*(R1+R2)/R2;
  
  // Print results to Serial Monitor to 2 decimal places
  return in_voltage;
}