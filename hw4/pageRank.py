import networkx as nx

G = nx.read_edgelist("/Users/shifanzhou/Downloads/edges.txt", create_using=nx.DiGraph())
pr = nx.pagerank(G,alpha=0.85,personalization=None,max_iter=30, tol=1e-06, nstart=None, weight='weight',dangling=None)

with open("external_pageRankFile.txt", "w") as f:
    for key, value in pr.items():
        f.write(f"/Users/shifanzhou/Downloads/foxnews/{key}={value}\n")