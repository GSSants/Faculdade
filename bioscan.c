#include <stdio.h>
#include <stdlib.h>
#include <string.h>

void busca_forca_bruta(const char *T, const char *P) {
    int n = strlen(T);
    int m = strlen(P);
    long long comparacoes = 0;

    printf("\n--- BUSCA POR FORCA BRUTA ---\n");

    for (int s = 0; s <= n - m; s++) {
        int j = 0;

        while (j < m) {
            comparacoes++;

            if (T[s + j] != P[j]) {
                break;
            }

            j++;
        }

        if (j == m) {
            printf("Padrao encontrado no indice: %d\n", s + 1);
        }
    }

    printf("Total de comparacoes (Forca Bruta): %lld\n", comparacoes);
}

int *computar_funcao_prefixo(const char *P, int m, long long *comparacoes) {
    int *pi = (int *) malloc(m * sizeof(int));

    if (pi == NULL) {
        fprintf(stderr, "Erro de alocacao de memoria.\n");
        exit(EXIT_FAILURE);
    }

    pi[0] = 0;
    int q = 0;

    for (int k = 1; k < m; k++) {
        while (q > 0) {
            (*comparacoes)++;

            if (P[k] == P[q]) {
                break;
            }

            q = pi[q - 1];
        }

        if (q == 0) {
            (*comparacoes)++;

            if (P[k] == P[q]) {
                q++;
            }
        } else {
            q++;
        }

        pi[k] = q;
    }

    return pi;
}

void busca_kmp(const char *T, const char *P) {
    int n = strlen(T);
    int m = strlen(P);
    long long comparacoes_pi = 0;
    long long comparacoes_busca = 0;

    int *pi = computar_funcao_prefixo(P, m, &comparacoes_pi);

    printf("\n--- ALGORITMO KMP ---\n");

    printf("Vetor PI: [");
    for (int i = 0; i < m; i++) {
        printf("%d", pi[i]);
        if (i < m - 1) {
            printf(", ");
        }
    }
    printf("]\n");

    int i = 0;
    int q = 0;

    while (i < n) {
        comparacoes_busca++;

        if (T[i] == P[q]) {
            i++;
            q++;

            if (q == m) {
                printf("Padrao encontrado no indice: %d\n", i - m + 1);
                q = pi[q - 1];
            }
        } else if (q > 0) {
            q = pi[q - 1];
        } else {
            i++;
        }
    }

    printf("Total de comparacoes (KMP): %lld\n", comparacoes_busca);

    free(pi);
}

int main(void) {
    char T[100005];
    char P[100005];

    if (scanf("%100004s", T) != 1 || scanf("%100004s", P) != 1) {
        return 0;
    }

    busca_forca_bruta(T, P);
    busca_kmp(T, P);

    return 0;
}
