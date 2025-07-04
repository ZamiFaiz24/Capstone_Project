import os
import pandas as pd
from sklearn.metrics import silhouette_score
from sklearn.metrics import mean_squared_error
from keras.models import Model
from keras.layers import Input, Dense
from keras.optimizers import Adam
from sklearn.preprocessing import MinMaxScaler
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
df = pd.read_csv(os.path.join(BASE_DIR, 'data_berat_intensitas.csv'))
df_200 = df.head(200)
print(df_200.head())

#cleaning data koma ke titik
df['berat'] = df['berat'].str.replace(',', '.', regex=False).astype(float)
df['intensitas'] = df['intensitas'].str.replace(',', '.', regex=False).astype(float)
print(df.head())

#scaling normalisasi
X = df[['berat', 'intensitas']]
scaler = MinMaxScaler()
X_scaled = scaler.fit_transform(X)

input_dim = X_scaled.shape[1]
encoding_dim = 2  # 2 dimensi untuk visualisasi

input_layer = Input(shape=(input_dim,))
encoded = Dense(16, activation='relu')(input_layer)
encoded = Dense(encoding_dim, activation='relu')(encoded)

decoded = Dense(16, activation='relu')(encoded)
decoded = Dense(input_dim, activation='sigmoid')(decoded)

autoencoder = Model(inputs=input_layer, outputs=decoded)
encoder = Model(inputs=input_layer, outputs=encoded)

optimizer = Adam(learning_rate=0.005)
autoencoder.compile(optimizer='adam', loss='mse')
history = autoencoder.fit(X_scaled, X_scaled, epochs=100, batch_size=4, verbose=0)

X_latent = encoder.predict(X_scaled)

#k-mens clustering
kmeans = KMeans(n_clusters=4, random_state=42)
labels = kmeans.fit_predict(X_latent)
print("Autoencoder input shape:", autoencoder.input_shape)

df['klaster_deep'] = labels
print("\nDataframe dengan Klaster:")
print(df.head(15))

#cek matrik evaluasi
score = silhouette_score(X_latent, labels) #mengukur kualitas clustering
print("Silhouette Score:", score) # yaitu seberapa baik setiap titik data
# berada di dalam klusternya dibandingkan dengan kluster lain.
# === Semakin tinggi nilainya semakin bagus klustering ===

X_reconstructed = autoencoder.predict(X_scaled)
mse = mean_squared_error(X_scaled, X_reconstructed)
print("Reconstruction MSE:", mse) #seberapa jauh hasil rekonstruksi autoencoder dibanding data input aslinya
# === Semakin mendekati 0, semakin bagus ===

#mencari jumlah klaster terbaik
scores = []
for k in range(2, 10):
    kmeans = KMeans(n_clusters=k, random_state=42)
    labels_k = kmeans.fit_predict(X_latent)
    score_k = silhouette_score(X_latent, labels_k)
    scores.append((k, score_k))
    print(f"K={k}, Silhouette Score={score_k:.4f}")